<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecordPaymentRequest;
use App\Models\Customer;
use App\Models\Installment;
use App\Repositories\Admin\InstallmentRepository;
use App\Services\Admin\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallmentController extends Controller
{
    public function __construct(
        protected InstallmentRepository $installmentRepository,
        protected PaymentService        $paymentService,
    ) {}

    public function index(Request $request): View
    {
        $this->installmentRepository->markLateInstallments();

        $filters = [
            'status'      => $request->get('status'),
            'customer_id' => $request->get('customer_id'),
            'date_from'   => $request->get('date_from'),
            'date_to'     => $request->get('date_to'),
        ];

        $installments = $this->installmentRepository->paginate(20, $filters);
        $counts       = $this->installmentRepository->countByStatus();
        $customers    = Customer::where('is_active', true)->orderBy('first_name')->get();

        return view('admin.installments.index', compact('installments', 'counts', 'customers'));
    }

    public function pay(Installment $installment): View
    {
        $installment->load('creditPlan.order.customer.company');

        if ($installment->status === Installment::STATUS_PAID) {
            abort(403, 'Cette échéance est déjà payée.');
        }

        return view('admin.installments.pay', compact('installment'));
    }

    public function recordPayment(RecordPaymentRequest $request, Installment $installment): RedirectResponse
    {
        try {
            $this->paymentService->recordPayment($installment, $request->validated());

            $installment->load('creditPlan.order');

            return redirect()->route('admin.orders.show', $installment->creditPlan->order)
                ->with('success', 'Paiement enregistré avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
}
