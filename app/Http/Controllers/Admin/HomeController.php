<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Installment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $now   = now();
        $month = $now->month;
        $year  = $now->year;

        // ── KPI cards ────────────────────────────────────────────────
        $stats = [
            'companies'        => Company::count(),
            'companies_active' => Company::where('is_active', true)->count(),
            'customers'        => Customer::count(),
            'customers_active' => Customer::where('is_active', true)->count(),
            'orders_total'     => Order::count(),
            'orders_month'     => Order::whereMonth('created_at', $month)->whereYear('created_at', $year)->count(),
            'orders_pending'   => Order::where('status', Order::STATUS_DRAFT)->count(),
            'revenue_month'    => Payment::whereMonth('payment_date', $month)->whereYear('payment_date', $year)->sum('amount'),
            'revenue_total'    => Payment::sum('amount'),
            'installments_late' => Installment::where('status', Installment::STATUS_LATE)->count(),
            'products_published' => Product::where('is_published', true)->count(),
            'users_active'     => User::where('is_active', true)->count(),
        ];

        // ── Commandes par statut ──────────────────────────────────────
        $ordersByStatus = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // ── Commandes récentes (10 dernières) ─────────────────────────
        $recentOrders = Order::with(['customer', 'items'])
            ->latest()
            ->limit(8)
            ->get();

        // ── Échéances à venir dans les 7 prochains jours ─────────────
        $upcomingInstallments = Installment::with(['creditPlan.order.customer'])
            ->whereIn('status', [Installment::STATUS_PENDING, Installment::STATUS_PARTIAL])
            ->whereBetween('due_date', [$now->toDateString(), $now->addDays(7)->toDateString()])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // ── Échéances en retard ───────────────────────────────────────
        $lateInstallments = Installment::with(['creditPlan.order.customer'])
            ->where('status', Installment::STATUS_LATE)
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // ── Revenus par mois (6 derniers mois) ───────────────────────
        $revenueChart = Payment::select(
                DB::raw("TO_CHAR(payment_date, 'Mon') as month_label"),
                DB::raw("DATE_TRUNC('month', payment_date) as month_start"),
                DB::raw('SUM(amount) as total')
            )
            ->where('payment_date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month_start', 'month_label')
            ->orderBy('month_start')
            ->get();

        // ── Top produits par nombre de lignes de commande ─────────────
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as qty'), DB::raw('SUM(order_items.line_total) as revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        return view('admin.home.index', compact(
            'stats',
            'ordersByStatus',
            'recentOrders',
            'upcomingInstallments',
            'lateInstallments',
            'revenueChart',
            'topProducts',
        ));
    }
}
