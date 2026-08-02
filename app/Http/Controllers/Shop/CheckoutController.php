<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DjomyTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Admin\OrderService;
use App\Services\DjomyService;
use App\Services\Shop\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /** Modes de livraison disponibles (Phase 1). */
    private const DELIVERY = [
        'standard' => ['label' => 'Livraison standard', 'fee' => 0],
        'express'  => ['label' => 'Livraison express', 'fee' => 50000],
    ];

    /** Échelonnements proposés au client rattaché à une entreprise. */
    private const CREDIT_PLANS = [3, 6, 9, 12];

    public function __construct(
        private readonly CartService $cart,
        private readonly DjomyService $djomy,
    ) {
    }

    public function index(): View|RedirectResponse
    {
        $summary = $this->cart->summary();

        if (empty($summary['items'])) {
            return redirect()->route('shop.cart.index')->with('success', 'Votre panier est vide.');
        }

        $customer = $this->currentCustomer();

        return view('shop.checkout', [
            'summary'    => $summary,
            'customer'   => $customer,
            'deliveries' => self::DELIVERY,
            // Crédit entreprise : proposé au client rattaché à une entreprise
            // dont le plafond disponible couvre le panier.
            'credit'     => $this->creditOffer($customer, (float) $summary['total']),
        ]);
    }

    /**
     * Conditions du paiement à crédit pour ce client et ce panier.
     * Retourne null si le crédit n'est pas ouvert.
     *
     * @return array{available: float|null, limit: float|null, plans: array<int>}|null
     */
    private function creditOffer(?Customer $customer, float $total): ?array
    {
        if (! $customer?->company_id) {
            return null;
        }

        $available = $customer->availableCredit();

        // availableCredit() vaut null quand aucun plafond n'est défini : pas de crédit.
        if (is_null($available) || $available <= 0 || $total > $available) {
            return null;
        }

        return [
            'available' => $available,
            'limit'     => $customer->effectiveCreditLimit(),
            'plans'     => self::CREDIT_PLANS,
        ];
    }

    public function store(Request $request, OrderService $orders): RedirectResponse
    {
        $summary = $this->cart->summary();

        if (empty($summary['items'])) {
            return redirect()->route('shop.cart.index')->with('success', 'Votre panier est vide.');
        }

        $customer = $this->currentCustomer();

        $credit = $this->creditOffer($customer, (float) $summary['total']);

        $data = $request->validate([
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'phone'           => ['required', 'string', 'max:30'],
            'address'         => ['required', 'string', 'max:255'],
            'city'            => ['required', 'string', 'max:100'],
            'delivery_method' => ['required', 'string', 'in:' . implode(',', array_keys(self::DELIVERY))],
            'payment_mode'    => ['nullable', 'string', 'in:cash,credit'],
            'installments'    => ['nullable', 'integer', 'in:' . implode(',', self::CREDIT_PLANS)],
            'down_payment'    => ['nullable', 'numeric', 'min:0', 'lt:' . (int) $summary['total']],
        ]);

        // Le crédit n'est retenu que s'il est réellement ouvert à ce client.
        $onCredit = ($data['payment_mode'] ?? 'cash') === 'credit' && $credit !== null;

        $deliveryFee = self::DELIVERY[$data['delivery_method']]['fee'];

        // 1) Création de la commande via le service existant.
        $order = $orders->createOrder([
            'items' => array_map(fn ($item) => [
                'product_id' => $item['product']->id,
                'variant_id' => $item['variant']?->id,
                'quantity'   => $item['quantity'],
            ], $summary['items']),
            'customer_id' => $customer->id,
            'order_type'  => $onCredit ? Order::TYPE_CREDIT : Order::TYPE_CASH,
            // Le crédit part en attente d'approbation du responsable d'entreprise.
            'pending_approval'          => $onCredit,
            'down_payment'              => $onCredit ? (float) ($data['down_payment'] ?? 0) : 0,
            'credit_installments_count' => $onCredit ? (int) ($data['installments'] ?? self::CREDIT_PLANS[0]) : null,
        ]);

        $shipping = [
            'shipping_name'    => trim($data['first_name'] . ' ' . $data['last_name']),
            'shipping_phone'   => $data['phone'],
            'shipping_address' => $data['address'],
            'shipping_city'    => $data['city'],
            'delivery_method'  => $data['delivery_method'],
            'delivery_fee'     => $deliveryFee,
            'discount_amount'  => $summary['discount'],
        ];

        // 1 bis) Crédit : pas de passage par Djomy, la commande attend son approbation.
        if ($onCredit) {
            $order->update($shipping + ['payment_method' => 'credit', 'payment_status' => 'pending']);

            $this->cart->clear();
            $this->cart->clearPromo();

            return redirect()->route('shop.account.orders.show', $order->id)->with(
                'success',
                "Commande {$order->order_number} soumise — en attente de validation par votre entreprise.",
            );
        }

        $order->update($shipping + [
            'status'         => Order::STATUS_PENDING_PAYMENT,
            'payment_status' => 'pending',
        ]);

        // Synchronise l'adresse/téléphone sur le profil client si absent.
        if (empty($customer->phone)) {
            $customer->update(['phone' => $data['phone']]);
        }

        // 2) Initialisation du paiement Djomy (redirection vers le portail).
        $amount    = (int) round($order->netTotal());
        $reference = 'SHOP-' . $order->id . '-' . strtoupper(Str::random(6));

        try {
            $resp = $this->djomy->createGatewayPayment([
                'amount'                   => $amount,
                'countryCode'              => config('djomy.country_code', 'GN'),
                'payerNumber'              => $this->normalizePhone($data['phone']),
                'description'              => 'Commande ' . $order->order_number . ' — CAURISHOP',
                'merchantPaymentReference' => $reference,
                'returnUrl'                => $this->callbackUrl('shop.checkout.return', ['ref' => $reference]),
                'cancelUrl'                => $this->callbackUrl('shop.checkout.cancel', ['ref' => $reference]),
                'metadata'                 => [
                    'order_id'    => (string) $order->id,
                    'customer_id' => (string) $customer->id,
                ],
            ]);

            $txnId      = $resp['data']['transactionId'] ?? $resp['transactionId'] ?? null;
            $paymentUrl = $resp['data']['redirectUrl'] ?? $resp['data']['paymentUrl']
                       ?? $resp['redirectUrl'] ?? $resp['paymentUrl'] ?? null;

            DjomyTransaction::create([
                'order_id'             => $order->id,
                'customer_id'          => $customer->id,
                'djomy_transaction_id' => $txnId,
                'merchant_reference'   => $reference,
                'amount'               => $amount,
                'status'               => DjomyTransaction::STATUS_PENDING,
                'payer_identifier'     => $data['phone'],
                'djomy_response'       => $resp,
            ]);

            $order->update(['payment_reference' => $reference, 'payment_method' => 'djomy']);

            if (! $paymentUrl) {
                throw new \RuntimeException('Aucune URL de redirection retournée par Djomy.');
            }

            // 3) Vidage du panier avant redirection vers le portail de paiement.
            $this->cart->clear();
            $this->cart->clearPromo();

            return redirect()->away($paymentUrl);
        } catch (\Throwable $e) {
            Log::error('Djomy shop checkout error', ['order' => $order->id, 'error' => $e->getMessage()]);

            return redirect()->route('shop.checkout.index')
                ->with('error', "Le paiement n'a pas pu être initialisé. Réessayez dans un instant.");
        }
    }

    /** Retour depuis le portail Djomy : vérifie le statut et redirige vers la confirmation. */
    public function paymentReturn(Request $request, OrderService $orders): RedirectResponse
    {
        $ref = $request->query('ref');
        $txn = $ref ? DjomyTransaction::where('merchant_reference', $ref)->first() : null;

        if (! $txn || ! $txn->order_id) {
            return redirect()->route('shop.account.orders')->with('error', 'Transaction introuvable.');
        }

        $this->syncTransactionStatus($txn, $orders);

        return redirect()->route('shop.checkout.confirmation', $txn->order_id);
    }

    /** Annulation depuis le portail Djomy. */
    public function paymentCancel(Request $request): RedirectResponse
    {
        $ref = $request->query('ref');
        $txn = $ref ? DjomyTransaction::where('merchant_reference', $ref)->first() : null;

        if ($txn && $txn->status === DjomyTransaction::STATUS_PENDING) {
            $txn->update(['status' => DjomyTransaction::STATUS_CANCELLED]);
        }

        return redirect()->route('shop.cart.index')->with('error', 'Paiement annulé.');
    }

    public function confirmation(Order $order): View
    {
        $this->authorizeOrder($order);
        $order->load(['items.product', 'items.variant', 'customer']);

        return view('shop.confirmation', compact('order'));
    }

    /** Interroge Djomy et règle la commande si le paiement est confirmé (idempotent). */
    private function syncTransactionStatus(DjomyTransaction $txn, OrderService $orders): void
    {
        if (! $txn->djomy_transaction_id) {
            return;
        }

        try {
            $resp   = $this->djomy->getPaymentStatus($txn->djomy_transaction_id);
            $data   = $resp['data'] ?? [];
            $status = strtoupper($data['status'] ?? $resp['status'] ?? '');

            $map = [
                'SUCCESS'   => DjomyTransaction::STATUS_SUCCESS,
                'FAILED'    => DjomyTransaction::STATUS_FAILED,
                'CANCELLED' => DjomyTransaction::STATUS_CANCELLED,
                'CANCELED'  => DjomyTransaction::STATUS_CANCELLED,
            ];

            if (! isset($map[$status])) {
                return; // toujours en attente
            }

            if ($txn->status !== $map[$status]) {
                $txn->update([
                    'status'         => $map[$status],
                    'payment_method' => $data['paymentMethod'] ?? $txn->payment_method,
                    'djomy_response' => array_merge($txn->djomy_response ?? [], ['return_check' => $resp]),
                ]);
            }

            if ($map[$status] === DjomyTransaction::STATUS_SUCCESS) {
                $this->settleOrder($txn, $data, $orders);
            }
        } catch (\Throwable $e) {
            Log::warning('Djomy shop return status check failed', ['ref' => $txn->merchant_reference, 'error' => $e->getMessage()]);
        }
    }

    /** Enregistre le paiement et confirme la commande (sans doublon avec le webhook). */
    private function settleOrder(DjomyTransaction $txn, array $data, OrderService $orders): void
    {
        $order = Order::find($txn->order_id);
        if (! $order) {
            return;
        }

        $paidAmount = (float) ($data['paidAmount'] ?? $txn->amount);

        Payment::firstOrCreate(
            ['reference' => $txn->merchant_reference],
            [
                'customer_id'  => $txn->customer_id,
                'order_id'     => $txn->order_id,
                'amount'       => $paidAmount,
                'payment_date' => now(),
                'method'       => Payment::METHOD_MOBILE_MONEY,
                'created_by'   => null,
            ]
        );

        $order->update([
            'payment_status'    => 'paid',
            'payment_reference' => $txn->merchant_reference,
        ]);

        if (in_array($order->status, [Order::STATUS_DRAFT, Order::STATUS_PENDING_PAYMENT])) {
            try {
                $orders->confirmOrder($order);
            } catch (\Throwable $e) {
                $order->update(['status' => Order::STATUS_CONFIRMED]);
            }
        }
    }

    private function callbackUrl(string $routeName, array $params = []): string
    {
        $base = rtrim(config('djomy.callback_url'), '/');
        $url  = $base . route($routeName, $params, false);

        return preg_replace('#^http://#', 'https://', $url);
    }

    private function normalizePhone(string $phone): string
    {
        $p = preg_replace('/[\s\-\(\)]/', '', $phone);
        return str_starts_with($p, '+') ? '00' . substr($p, 1) : $p;
    }

    private function currentCustomer(): Customer
    {
        $user = Auth::user();

        return $user->customer ?? Customer::create([
            'user_id'    => $user->id,
            'type'       => Customer::TYPE_INDIVIDUAL,
            'company_id' => null,
            'first_name' => $user->name,
            'last_name'  => '',
            'email'      => $user->email,
            'is_active'  => true,
        ]);
    }

    private function authorizeOrder(Order $order): void
    {
        $customer = Auth::user()->customer;
        abort_unless($customer && $order->customer_id === $customer->id, 403);
    }
}
