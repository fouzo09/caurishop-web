<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\Admin\ActivityLogService;
use App\Services\Admin\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): View
    {
        $company     = auth()->user()->company;
        $customerIds = Customer::where('company_id', $company->id)->pluck('id');

        $status = $request->get('status');
        $query  = Order::with(['customer', 'items'])
            ->whereIn('customer_id', $customerIds)
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $orders  = $query->paginate(20);
        $pending = Order::whereIn('customer_id', $customerIds)
            ->where('status', Order::STATUS_PENDING_APPROVAL)->count();

        return view('company.orders.index', compact('orders', 'pending', 'status', 'company'));
    }

    public function show(Order $order): View
    {
        $this->authorizeOrder($order);
        $order->load(['customer', 'items.product', 'items.variant', 'creditPlan.installments']);

        return view('company.orders.show', compact('order'));
    }

    public function approve(Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

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

    public function reject(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

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

    /**
     * L'admin entreprise peut aussi passer une commande pour lui-même
     * (ça crée une commande en STATUS_DRAFT directement).
     */
    public function create(): View
    {
        $products = Product::where('is_published', true)->where('is_active', true)
            ->with('variants')->get();
        $company  = auth()->user()->company;

        // Trouver ou créer le Customer lié à cet utilisateur
        $customer = $this->resolveCustomer();

        return view('company.orders.create', compact('products', 'company', 'customer'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'order_type'     => 'required|in:cash,credit',
            'down_payment'   => 'nullable|numeric|min:0',
            'credit_installments_count' => 'nullable|integer|min:1',
        ]);

        try {
            $customer = $this->resolveCustomer();

            $data = $request->only(['items', 'order_type', 'down_payment', 'credit_installments_count']);
            $data['customer_id'] = $customer->id;
            // L'admin entreprise bypass la pré-validation → direct DRAFT
            $data['bypass_approval'] = true;

            $order = $this->orderService->createOrder($data);

            return redirect()->route('company.orders.show', $order->id)
                ->with('success', "Commande {$order->order_number} créée avec succès.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function authorizeOrder(Order $order): void
    {
        $company     = auth()->user()->company;
        $customerIds = Customer::where('company_id', $company->id)->pluck('id');

        abort_unless($customerIds->contains($order->customer_id), 403);
    }

    private function resolveCustomer(): Customer
    {
        $user = auth()->user();

        $customer = Customer::where('user_id', $user->id)->first();

        if (!$customer) {
            $customer = Customer::create([
                'user_id'    => $user->id,
                'type'       => Customer::TYPE_COMPANY,
                'company_id' => $user->company_id,
                'first_name' => explode(' ', $user->name)[0] ?? $user->name,
                'last_name'  => implode(' ', array_slice(explode(' ', $user->name), 1)) ?: '',
                'email'      => $user->email,
                'is_active'  => true,
            ]);
        }

        return $customer;
    }
}
