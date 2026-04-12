<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Repositories\Admin\PaymentRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentRepository $paymentRepository,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'customer_id' => $request->get('customer_id'),
            'method'      => $request->get('method'),
            'date_from'   => $request->get('date_from'),
            'date_to'     => $request->get('date_to'),
        ];

        $payments  = $this->paymentRepository->paginate(20, $filters);
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();

        return view('admin.payments.index', compact('payments', 'customers'));
    }
}
