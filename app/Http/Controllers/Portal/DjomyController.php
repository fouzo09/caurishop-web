<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DjomyTransaction;
use App\Models\Installment;
use App\Models\Order;
use App\Models\Payment;
use App\Services\DjomyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DjomyController extends Controller
{
    public function __construct(protected DjomyService $djomyService) {}

    private function callbackUrl(string $routeName, array $params = []): string
    {
        $base = rtrim(config('djomy.callback_url'), '/');
        $path = route($routeName, $params, false);
        $url  = $base . $path;
        return preg_replace('#^http://#', 'https://', $url);
    }

    // GET /portal/djomy/checkout/{installment}
    public function checkout(Installment $installment)
    {
        $customer = Customer::where('user_id', auth()->id())->firstOrFail();
        abort_unless($installment->creditPlan?->order?->customer_id === $customer->id, 403);
        abort_if($installment->status === Installment::STATUS_PAID, 422, 'Cette échéance est déjà réglée.');

        return view('portal.djomy.checkout', compact('installment', 'customer'));
    }

    // POST /portal/djomy/checkout/{installment}
    public function initiateCheckout(Request $request, Installment $installment)
    {
        $customer = Customer::where('user_id', auth()->id())->firstOrFail();
        abort_unless($installment->creditPlan?->order?->customer_id === $customer->id, 403);
        abort_if($installment->status === Installment::STATUS_PAID, 422, 'Cette échéance est déjà réglée.');

        if (empty($customer->phone)) {
            return redirect()->route('portal.profile')
                ->with('error', 'Veuillez renseigner votre numéro de téléphone avant de procéder au paiement.');
        }

        $remaining  = (int) round((float) $installment->amount_due - (float) $installment->amount_paid);
        $order      = $installment->creditPlan->order;
        $reference  = 'INST-' . $installment->id . '-' . strtoupper(Str::random(6));

        try {
            $djomyResp = $this->djomyService->createGatewayPayment([
                'amount'                   => $remaining,
                'countryCode'              => config('djomy.country_code', 'GN'),
                'payerNumber'              => preg_replace('/[\s\-]/', '', $customer->phone ?? ''),
                'description'              => "Échéance #{$installment->installment_number} – Commande {$order->order_number}",
                'merchantPaymentReference' => $reference,
                'returnUrl'                => $this->callbackUrl('portal.djomy.return', ['ref' => $reference]),
                'cancelUrl'                => $this->callbackUrl('portal.djomy.cancel', ['ref' => $reference]),
                'metadata'                 => [
                    'installment_id' => (string) $installment->id,
                    'order_id'       => (string) $order->id,
                    'customer_id'    => (string) $customer->id,
                ],
            ]);

            $txnId      = $djomyResp['data']['transactionId'] ?? $djomyResp['transactionId'] ?? null;
            $paymentUrl = $djomyResp['data']['redirectUrl']   ?? $djomyResp['data']['paymentUrl']
                       ?? $djomyResp['redirectUrl']           ?? $djomyResp['paymentUrl']    ?? null;

            DjomyTransaction::create([
                'installment_id'       => $installment->id,
                'order_id'             => $order->id,
                'customer_id'          => $customer->id,
                'djomy_transaction_id' => $txnId,
                'merchant_reference'   => $reference,
                'amount'               => $remaining,
                'status'               => DjomyTransaction::STATUS_PENDING,
                'payer_identifier'     => $customer->phone,
                'djomy_response'       => $djomyResp,
            ]);

            if (!$paymentUrl) {
                throw new \RuntimeException('Aucune URL de redirection retournée. Réponse Djomy : ' . json_encode($djomyResp));
            }

            return redirect()->away($paymentUrl);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'initialisation du paiement : ' . $e->getMessage());
        }
    }

    // GET /portal/djomy/return?ref=XXX
    public function return(Request $request)
    {
        $ref = $request->query('ref');
        $txn = $ref ? DjomyTransaction::where('merchant_reference', $ref)->first() : null;

        if (!$txn) {
            return redirect()->route('portal.orders.index')->with('error', 'Transaction introuvable.');
        }

        return view('portal.djomy.return', ['ref' => $ref, 'txn' => $txn]);
    }

    // GET /portal/djomy/check-status?ref=XXX  (appelé en AJAX depuis la page return)
    public function checkStatus(Request $request)
    {
        $ref = $request->query('ref');
        $txn = $ref ? DjomyTransaction::where('merchant_reference', $ref)->first() : null;

        if (!$txn || !$txn->djomy_transaction_id) {
            return response()->json(['status' => 'error', 'message' => 'Transaction introuvable.'], 404);
        }

        try {
            $resp       = $this->djomyService->getPaymentStatus($txn->djomy_transaction_id);
            $rawStatus  = strtoupper($resp['data']['status'] ?? $resp['status'] ?? '');

            $map = [
                'SUCCESS'   => DjomyTransaction::STATUS_SUCCESS,
                'FAILED'    => DjomyTransaction::STATUS_FAILED,
                'CANCELLED' => DjomyTransaction::STATUS_CANCELLED,
                'CANCELED'  => DjomyTransaction::STATUS_CANCELLED,
            ];

            if (!isset($map[$rawStatus])) {
                return response()->json(['status' => 'pending', 'message' => 'Paiement en cours de traitement…']);
            }

            $finalStatus = $map[$rawStatus];

            if ($txn->status !== $finalStatus) {
                $txn->update([
                    'status'         => $finalStatus,
                    'payment_method' => $resp['data']['paymentMethod'] ?? $txn->payment_method,
                    'djomy_response' => array_merge($txn->djomy_response ?? [], ['return_check' => $resp]),
                ]);
            }

            if ($finalStatus === DjomyTransaction::STATUS_SUCCESS) {
                $data = $resp['data'] ?? [];
                if ($txn->installment_id) {
                    $this->recordInstallmentPayment($txn, $data);
                } elseif ($txn->order_id) {
                    $this->recordOrderPayment($txn, $data);
                }
            }

            if ($finalStatus === DjomyTransaction::STATUS_SUCCESS) {
                $redirect = $txn->installment_id
                    ? route('portal.payments.index')
                    : route('portal.orders.show', $txn->order_id);

                return response()->json([
                    'status'   => 'success',
                    'message'  => 'Paiement confirmé avec succès !',
                    'redirect' => $redirect,
                ]);
            }

            $redirect = $txn->installment_id
                ? route('portal.payments.index')
                : route('portal.orders.show', $txn->order_id);

            return response()->json([
                'status'   => 'failed',
                'message'  => 'Le paiement a échoué ou a été annulé.',
                'redirect' => $redirect,
            ]);

        } catch (\Throwable $e) {
            Log::error('checkStatus error', ['ref' => $ref, 'error' => $e->getMessage()]);
            return response()->json(['status' => 'pending', 'message' => 'Vérification en cours…']);
        }
    }

    // GET /portal/djomy/cancel?ref=XXX
    public function cancel(Request $request)
    {
        $ref = $request->query('ref');
        $txn = $ref ? DjomyTransaction::where('merchant_reference', $ref)->first() : null;

        if ($txn && $txn->status === DjomyTransaction::STATUS_PENDING) {
            $txn->update(['status' => DjomyTransaction::STATUS_CANCELLED]);
        }

        return redirect()->route('portal.payments.index')
            ->with('error', 'Paiement annulé.');
    }

    // GET /portal/djomy/order-checkout/{order}
    public function checkoutOrder(Order $order)
    {
        $customer = Customer::where('user_id', auth()->id())->firstOrFail();
        abort_unless($order->customer_id === $customer->id, 403);
        abort_unless(in_array($order->status, [Order::STATUS_DRAFT, Order::STATUS_PENDING_PAYMENT]), 422, 'Cette commande ne peut plus être payée.');

        $order->load('items.product');
        return view('portal.djomy.checkout-order', compact('order', 'customer'));
    }

    // POST /portal/djomy/order-checkout/{order}
    public function initiateOrderCheckout(Request $request, Order $order)
    {
        $customer = Customer::where('user_id', auth()->id())->firstOrFail();
        abort_unless($order->customer_id === $customer->id, 403);
        abort_unless(in_array($order->status, [Order::STATUS_DRAFT, Order::STATUS_PENDING_PAYMENT]), 422, 'Cette commande ne peut plus être payée.');

        if (empty($customer->phone)) {
            return redirect()->route('portal.profile')
                ->with('error', 'Veuillez renseigner votre numéro de téléphone avant de procéder au paiement.');
        }

        $amount    = (int) round((float) $order->total_amount);
        $reference = 'ORD-' . $order->id . '-' . strtoupper(Str::random(6));

        try {
            $djomyResp = $this->djomyService->createGatewayPayment([
                'amount'                   => $amount,
                'countryCode'              => config('djomy.country_code', 'GN'),
                'payerNumber'              => preg_replace('/[\s\-]/', '', $customer->phone ?? ''),
                'description'              => "Paiement commande {$order->order_number}",
                'merchantPaymentReference' => $reference,
                'returnUrl'                => $this->callbackUrl('portal.djomy.return', ['ref' => $reference]),
                'cancelUrl'                => $this->callbackUrl('portal.djomy.cancel', ['ref' => $reference]),
                'metadata'                 => [
                    'order_id'    => (string) $order->id,
                    'customer_id' => (string) $customer->id,
                ],
            ]);

            $txnId      = $djomyResp['data']['transactionId'] ?? $djomyResp['transactionId'] ?? null;
            $paymentUrl = $djomyResp['data']['redirectUrl']   ?? $djomyResp['data']['paymentUrl']
                       ?? $djomyResp['redirectUrl']           ?? $djomyResp['paymentUrl']    ?? null;

            DjomyTransaction::create([
                'installment_id'       => null,
                'order_id'             => $order->id,
                'customer_id'          => $customer->id,
                'djomy_transaction_id' => $txnId,
                'merchant_reference'   => $reference,
                'amount'               => $amount,
                'status'               => DjomyTransaction::STATUS_PENDING,
                'payer_identifier'     => $customer->phone,
                'djomy_response'       => $djomyResp,
            ]);

            if (!$paymentUrl) {
                throw new \RuntimeException('Aucune URL de redirection retournée. Réponse Djomy : ' . json_encode($djomyResp));
            }

            return redirect()->away($paymentUrl);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'initialisation du paiement : ' . $e->getMessage());
        }
    }

    // POST /djomy/webhook  (public — no auth, no CSRF)
    public function webhook(Request $request)
    {
        $rawPayload = $request->getContent();
        $signature  = $request->header('X-Webhook-Signature', '');

        if (!$this->djomyService->verifyWebhook($rawPayload, $signature)) {
            Log::warning('Djomy webhook: invalid signature', ['sig' => $signature]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload   = json_decode($rawPayload, true);
        $eventType = $payload['eventType'] ?? '';
        $data      = $payload['data'] ?? [];
        $txnId     = $data['transactionId'] ?? null;
        $refId     = $data['merchantPaymentReference'] ?? null;

        $txn = $txnId
            ? DjomyTransaction::where('djomy_transaction_id', $txnId)->first()
            : ($refId ? DjomyTransaction::where('merchant_reference', $refId)->first() : null);

        if (!$txn) {
            Log::warning('Djomy webhook: transaction not found', ['eventType' => $eventType, 'txnId' => $txnId]);
            return response()->json(['status' => 'not_found'], 404);
        }

        $statusMap = [
            'payment.success'   => DjomyTransaction::STATUS_SUCCESS,
            'payment.failed'    => DjomyTransaction::STATUS_FAILED,
            'payment.cancelled' => DjomyTransaction::STATUS_CANCELLED,
        ];

        if (isset($statusMap[$eventType])) {
            $txn->update([
                'status'         => $statusMap[$eventType],
                'payment_method' => $data['paymentMethod'] ?? $txn->payment_method,
                'djomy_response' => array_merge($txn->djomy_response ?? [], ['webhook_event' => $payload]),
            ]);
        }

        if ($eventType === 'payment.success') {
            if ($txn->installment_id) {
                $this->recordInstallmentPayment($txn, $data);
            } elseif ($txn->order_id) {
                $this->recordOrderPayment($txn, $data);
            }
        }

        Log::info('Djomy webhook processed', ['event' => $eventType, 'txn' => $txn->id]);

        return response()->json(['status' => 'ok']);
    }

    private function recordOrderPayment(DjomyTransaction $txn, array $data): void
    {
        try {
            $order = Order::find($txn->order_id);
            if (!$order) return;

            $paidAmount = (float) ($data['paidAmount'] ?? $txn->amount);

            Payment::create([
                'customer_id' => $txn->customer_id,
                'order_id'    => $txn->order_id,
                'amount'      => $paidAmount,
                'payment_date'=> now(),
                'method'      => Payment::METHOD_MOBILE_MONEY,
                'reference'   => $txn->merchant_reference,
                'created_by'  => null,
            ]);

            $order->update(['status' => Order::STATUS_CONFIRMED]);
        } catch (\Throwable $e) {
            Log::error('Djomy: failed to record order payment', [
                'txn_id' => $txn->id,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    private function recordInstallmentPayment(DjomyTransaction $txn, array $data): void
    {
        try {
            $installment = Installment::find($txn->installment_id);
            if (!$installment) return;

            $paidAmount = (float) ($data['paidAmount'] ?? $txn->amount);

            Payment::create([
                'customer_id'    => $txn->customer_id,
                'order_id'       => $txn->order_id,
                'credit_plan_id' => $installment->credit_plan_id,
                'amount'         => $paidAmount,
                'payment_date'   => now(),
                'method'         => Payment::METHOD_MOBILE_MONEY,
                'reference'      => $txn->merchant_reference,
                'created_by'     => null,
            ]);

            $newPaid = (float) $installment->amount_paid + $paidAmount;
            $status  = $newPaid >= (float) $installment->amount_due
                ? Installment::STATUS_PAID
                : Installment::STATUS_PARTIAL;

            $installment->update([
                'amount_paid' => $newPaid,
                'status'      => $status,
            ]);
        } catch (\Throwable $e) {
            Log::error('Djomy: failed to record installment payment', [
                'txn_id' => $txn->id,
                'error'  => $e->getMessage(),
            ]);
        }
    }
}
