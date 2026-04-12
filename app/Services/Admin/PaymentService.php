<?php

namespace App\Services\Admin;

use App\Models\CreditPlan;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Services\Admin\NotificationService;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function recordPayment(Installment $installment, array $data): Payment
    {
        $installment->load('creditPlan');

        $remaining = (float) $installment->amount_due - (float) $installment->amount_paid;

        if ((float) $data['amount'] > $remaining + 0.01) {
            throw new \Exception(
                'Le montant dépasse le reste dû (' . number_format($remaining, 0, ',', ' ') . ' GNF).'
            );
        }

        return DB::transaction(function () use ($installment, $data, $remaining) {
            $payment = Payment::create([
                'customer_id'    => $installment->creditPlan->order->customer_id,
                'order_id'       => $installment->creditPlan->order_id,
                'credit_plan_id' => $installment->credit_plan_id,
                'amount'         => $data['amount'],
                'payment_date'   => $data['payment_date'],
                'method'         => $data['method'],
                'reference'      => $data['reference'] ?? null,
                'created_by'     => auth()->id(),
            ]);

            PaymentAllocation::create([
                'payment_id'       => $payment->id,
                'installment_id'   => $installment->id,
                'amount_allocated' => $data['amount'],
            ]);

            $newAmountPaid = (float) $installment->amount_paid + (float) $data['amount'];
            $newStatus     = $newAmountPaid >= (float) $installment->amount_due
                ? Installment::STATUS_PAID
                : Installment::STATUS_PARTIAL;

            $installment->update([
                'amount_paid' => $newAmountPaid,
                'status'      => $newStatus,
            ]);

            $plan = $installment->creditPlan;
            $newOutstanding = max(0, (float) $plan->outstanding_amount - (float) $data['amount']);
            $plan->update(['outstanding_amount' => $newOutstanding]);

            $allPaid = $plan->installments()->whereNotIn('status', [Installment::STATUS_PAID])->doesntExist();
            if ($allPaid) {
                $plan->update(['status' => CreditPlan::STATUS_CLOSED, 'outstanding_amount' => 0]);
            }

            $customer = $installment->creditPlan->order->customer;
            $customerName = trim(($customer?->first_name ?? '') . ' ' . ($customer?->last_name ?? ''));
            app(NotificationService::class)->paymentReceived(
                number_format($data['amount'], 0, ',', ' '),
                $customerName,
                route('admin.payments.index'),
            );

            return $payment;
        });
    }
}
