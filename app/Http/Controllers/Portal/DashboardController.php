<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Installment;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $customer = Customer::where('user_id', $user->id)->first();

        $stats = [
            'orders_total'   => $customer ? Order::where('customer_id', $customer->id)->count() : 0,
            'orders_pending' => $customer ? Order::where('customer_id', $customer->id)
                                              ->where('status', Order::STATUS_PENDING_APPROVAL)->count() : 0,
            'late_payments'  => $customer ? Installment::whereHas('creditPlan.order',
                                              fn ($q) => $q->where('customer_id', $customer->id))
                                              ->where('status', Installment::STATUS_LATE)->count() : 0,
            'products_count' => Product::where('is_published', true)->where('is_active', true)->count(),
        ];

        $recentOrders = $customer
            ? Order::with(['items.product'])
                ->where('customer_id', $customer->id)
                ->latest()->limit(5)->get()
            : collect();

        $upcomingInstallments = $customer
            ? Installment::with(['creditPlan.order'])
                ->whereHas('creditPlan.order', fn ($q) => $q->where('customer_id', $customer->id))
                ->whereIn('status', [Installment::STATUS_PENDING, Installment::STATUS_PARTIAL])
                ->orderBy('due_date')->limit(3)->get()
            : collect();

        return view('portal.dashboard.index', compact(
            'user', 'customer', 'stats', 'recentOrders', 'upcomingInstallments'
        ));
    }
}
