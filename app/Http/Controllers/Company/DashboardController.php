<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Installment;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $company   = auth()->user()->company;
        $companyId = $company->id;

        // IDs des clients de cette entreprise
        $customerIds = Customer::where('company_id', $companyId)->pluck('id');

        $stats = [
            'employees'       => Customer::where('company_id', $companyId)->count(),
            'pending'         => Order::whereIn('customer_id', $customerIds)
                                      ->where('status', Order::STATUS_PENDING_APPROVAL)->count(),
            'orders_total'    => Order::whereIn('customer_id', $customerIds)->count(),
            'orders_month'    => Order::whereIn('customer_id', $customerIds)
                                      ->whereMonth('created_at', now()->month)
                                      ->whereYear('created_at', now()->year)->count(),
            'late_installments' => Installment::whereHas('creditPlan.order', fn ($q) =>
                                        $q->whereIn('customer_id', $customerIds))
                                      ->where('status', Installment::STATUS_LATE)->count(),
        ];

        $pendingOrders = Order::with(['customer', 'items'])
            ->whereIn('customer_id', $customerIds)
            ->where('status', Order::STATUS_PENDING_APPROVAL)
            ->latest()->limit(10)->get();

        $recentOrders = Order::with(['customer', 'items'])
            ->whereIn('customer_id', $customerIds)
            ->whereNotIn('status', [Order::STATUS_PENDING_APPROVAL])
            ->latest()->limit(8)->get();

        return view('company.dashboard.index', compact(
            'company', 'stats', 'pendingOrders', 'recentOrders'
        ));
    }
}
