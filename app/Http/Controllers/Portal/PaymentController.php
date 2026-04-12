<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Installment;
use App\Models\Payment;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $user     = auth()->user();
        $customer = Customer::where('user_id', $user->id)->first();

        $installments = $customer
            ? Installment::with(['creditPlan.order'])
                ->whereHas('creditPlan.order', fn ($q) => $q->where('customer_id', $customer->id))
                ->orderBy('due_date')
                ->paginate(20)
            : collect();

        $payments = $customer
            ? Payment::with(['order'])
                ->where('customer_id', $customer->id)
                ->latest('payment_date')
                ->limit(10)->get()
            : collect();

        $stats = $customer ? [
            'total_paid'    => Payment::where('customer_id', $customer->id)->sum('amount'),
            'pending_count' => Installment::whereHas('creditPlan.order',
                                fn ($q) => $q->where('customer_id', $customer->id))
                                ->whereIn('status', [Installment::STATUS_PENDING, Installment::STATUS_PARTIAL])
                                ->count(),
            'late_count'    => Installment::whereHas('creditPlan.order',
                                fn ($q) => $q->where('customer_id', $customer->id))
                                ->where('status', Installment::STATUS_LATE)
                                ->count(),
        ] : ['total_paid' => 0, 'pending_count' => 0, 'late_count' => 0];

        return view('portal.payments.index', compact('installments', 'payments', 'stats'));
    }
}
