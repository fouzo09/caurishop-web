<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Installment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Admin\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Volet « entreprise » de l'espace client.
 *
 * Les salariés et les administrateurs d'entreprise sont des clients comme les
 * autres : ces pages vivent donc dans /mon-compte, avec la même UI que le reste
 * du storefront. Elles reprennent les fonctionnalités des anciens espaces
 * /portal (échéances de crédit) et /company (salariés, commandes, entreprise).
 */
class CompanyController extends Controller
{
    /** Échéances et paiements du client — visible dès qu'il est rattaché à une entreprise. */
    public function payments(): View
    {
        $customer = $this->customer();
        abort_unless($customer?->company_id, 403);

        $installments = Installment::with(['creditPlan.order'])
            ->whereHas('creditPlan.order', fn ($q) => $q->where('customer_id', $customer->id))
            ->orderBy('due_date')
            ->paginate(20);

        $payments = Payment::with('order')
            ->where('customer_id', $customer->id)
            ->latest('payment_date')
            ->limit(10)
            ->get();

        $pendingQuery = fn () => Installment::whereHas(
            'creditPlan.order',
            fn ($q) => $q->where('customer_id', $customer->id)
        );

        $stats = [
            'total_paid'    => Payment::where('customer_id', $customer->id)->sum('amount'),
            'pending_count' => $pendingQuery()->whereIn('status', [Installment::STATUS_PENDING, Installment::STATUS_PARTIAL])->count(),
            'late_count'    => $pendingQuery()->where('status', Installment::STATUS_LATE)->count(),
            'credit_limit'  => $customer->effectiveCreditLimit(),
        ];

        return view('shop.account.payments', compact('installments', 'payments', 'stats', 'customer'));
    }

    /** Fiche de l'entreprise du client. */
    public function company(): View
    {
        $company = $this->currentCompany();

        $customerIds = Customer::where('company_id', $company->id)->pluck('id');

        $stats = [
            'staff'    => User::role('company_employee')->where('company_id', $company->id)->count(),
            'orders'   => Order::whereIn('customer_id', $customerIds)->count(),
            'pending'  => Order::whereIn('customer_id', $customerIds)->where('status', Order::STATUS_PENDING_APPROVAL)->count(),
            'spent'    => Order::whereIn('customer_id', $customerIds)->sum('total_amount'),
        ];

        return view('shop.account.company', compact('company', 'stats'));
    }

    /** Salariés rattachés à l'entreprise. */
    public function staff(): View
    {
        $company = $this->currentCompany();

        $employees = User::role('company_employee')
            ->where('company_id', $company->id)
            ->with('customer')
            ->orderBy('name')
            ->get();

        return view('shop.account.company-staff', compact('company', 'employees'));
    }

    /** Commandes passées par les salariés de l'entreprise. */
    public function orders(Request $request): View
    {
        $company     = $this->currentCompany();
        $customerIds = Customer::where('company_id', $company->id)->pluck('id');

        $status = $request->get('status');

        $orders = Order::with(['customer', 'items'])
            ->whereIn('customer_id', $customerIds)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $pending = Order::whereIn('customer_id', $customerIds)
            ->where('status', Order::STATUS_PENDING_APPROVAL)
            ->count();

        return view('shop.account.company-orders', compact('orders', 'pending', 'status', 'company'));
    }

    public function showOrder(Order $order): View
    {
        $this->authorizeCompanyOrder($order);
        $order->load(['customer', 'items.product', 'items.variant', 'creditPlan.installments']);

        return view('shop.account.company-order', compact('order'));
    }

    public function approve(Order $order): RedirectResponse
    {
        $this->authorizeCompanyOrder($order);

        if ($order->status !== Order::STATUS_PENDING_APPROVAL) {
            return back()->with('error', 'Cette commande ne peut pas être approuvée.');
        }

        $order->update(['status' => Order::STATUS_DRAFT]);

        app(ActivityLogService::class)->log(
            'order_approved',
            "Commande {$order->order_number} approuvée par l'admin entreprise",
        );

        return back()->with('success', "Commande {$order->order_number} approuvée — transmise au traitement.");
    }

    public function reject(Order $order): RedirectResponse
    {
        $this->authorizeCompanyOrder($order);

        if ($order->status !== Order::STATUS_PENDING_APPROVAL) {
            return back()->with('error', 'Cette commande ne peut pas être rejetée.');
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        app(ActivityLogService::class)->log(
            'order_rejected',
            "Commande {$order->order_number} rejetée par l'admin entreprise",
        );

        return back()->with('success', "Commande {$order->order_number} rejetée.");
    }

    private function customer(): ?Customer
    {
        return auth()->user()->customer;
    }

    private function currentCompany(): Company
    {
        $company = auth()->user()->company;
        abort_unless($company, 403);

        return $company;
    }

    private function authorizeCompanyOrder(Order $order): void
    {
        $company = $this->currentCompany();
        abort_unless($order->customer && $order->customer->company_id === $company->id, 403);
    }
}
