<?php

namespace App\Console\Commands;

use App\Models\DjomyTransaction;
use App\Models\Installment;
use App\Models\Order;
use App\Models\Payment;
use App\Services\DjomyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DjomySyncPending extends Command
{
    protected $signature   = 'djomy:sync-pending';
    protected $description = 'Vérifie les transactions Djomy en attente et met à jour leur statut';

    public function __construct(protected DjomyService $djomyService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $pending = DjomyTransaction::where('status', DjomyTransaction::STATUS_PENDING)
            ->whereNotNull('djomy_transaction_id')
            ->get();

        if ($pending->isEmpty()) {
            $this->info('Aucune transaction en attente.');
            return 0;
        }

        $this->info("Vérification de {$pending->count()} transaction(s) en attente…");

        foreach ($pending as $txn) {
            $this->processTxn($txn);
        }

        $this->info('Synchronisation terminée.');
        return 0;
    }

    private function processTxn(DjomyTransaction $txn): void
    {
        try {
            $resp   = $this->djomyService->getPaymentStatus($txn->djomy_transaction_id);
            $status = strtoupper($resp['data']['status'] ?? $resp['status'] ?? '');

            $this->line("  [{$txn->merchant_reference}] → {$status}");

            $map = [
                'SUCCESS'   => DjomyTransaction::STATUS_SUCCESS,
                'FAILED'    => DjomyTransaction::STATUS_FAILED,
                'CANCELLED' => DjomyTransaction::STATUS_CANCELLED,
                'CANCELED'  => DjomyTransaction::STATUS_CANCELLED,
            ];

            if (!isset($map[$status])) {
                return; // toujours en cours
            }

            $txn->update([
                'status'         => $map[$status],
                'payment_method' => $resp['data']['paymentMethod'] ?? $txn->payment_method,
                'djomy_response' => array_merge($txn->djomy_response ?? [], ['sync_check' => $resp]),
            ]);

            if ($map[$status] === DjomyTransaction::STATUS_SUCCESS) {
                $data = $resp['data'] ?? [];
                if ($txn->installment_id) {
                    $this->recordInstallmentPayment($txn, $data);
                } elseif ($txn->order_id) {
                    $this->recordOrderPayment($txn, $data);
                }
            }
        } catch (\Throwable $e) {
            $this->warn("  Erreur pour {$txn->merchant_reference} : " . $e->getMessage());
            Log::error('DjomySyncPending: erreur', ['txn' => $txn->id, 'error' => $e->getMessage()]);
        }
    }

    private function recordOrderPayment(DjomyTransaction $txn, array $data): void
    {
        $order = Order::find($txn->order_id);
        if (!$order || $order->status === Order::STATUS_CONFIRMED) return;

        $paidAmount = (float) ($data['paidAmount'] ?? $txn->amount);

        Payment::create([
            'customer_id'  => $txn->customer_id,
            'order_id'     => $txn->order_id,
            'amount'       => $paidAmount,
            'payment_date' => now(),
            'method'       => Payment::METHOD_MOBILE_MONEY,
            'reference'    => $txn->merchant_reference,
            'created_by'   => null,
        ]);

        $order->update(['status' => Order::STATUS_CONFIRMED]);
        $this->info("    → Commande #{$order->order_number} confirmée.");
    }

    private function recordInstallmentPayment(DjomyTransaction $txn, array $data): void
    {
        $installment = Installment::find($txn->installment_id);
        if (!$installment || $installment->status === Installment::STATUS_PAID) return;

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

        $installment->update(['amount_paid' => $newPaid, 'status' => $status]);
        $this->info("    → Échéance #{$installment->installment_number} mise à jour ({$status}).");
    }
}
