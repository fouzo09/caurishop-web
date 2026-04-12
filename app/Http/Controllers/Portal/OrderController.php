<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\Admin\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(): View
    {
        $customer = Customer::where('user_id', auth()->id())->first();

        $orders = $customer
            ? Order::with(['items.product'])
                ->where('customer_id', $customer->id)
                ->latest()
                ->paginate(15)
            : collect();

        return view('portal.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $customer = $this->getOrAbort();
        abort_unless($order->customer_id === $customer->id, 403);

        $order->load(['items.product', 'items.variant', 'creditPlan.installments']);

        return view('portal.orders.show', compact('order'));
    }

    public function create(): View
    {
        $products = Product::where('is_published', true)->where('is_active', true)
            ->with(['variants', 'images'])->get();

        $company = auth()->user()->company;

        return view('portal.orders.create', compact('products', 'company'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'order_type'         => 'required|in:cash,credit',
            'down_payment'       => 'nullable|numeric|min:0',
            'credit_installments_count' => 'nullable|integer|min:1',
        ]);

        try {
            $customer = $this->resolveCustomer();

            $data = $request->only(['items', 'order_type', 'down_payment', 'credit_installments_count']);
            $data['customer_id']      = $customer->id;
            $data['pending_approval'] = true; // force pending_approval

            $order = $this->orderService->createOrder($data);

            return redirect()->route('portal.orders.show', $order->id)
                ->with('success', "Commande {$order->order_number} soumise — en attente de validation de votre responsable.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function getOrAbort(): Customer
    {
        $customer = Customer::where('user_id', auth()->id())->first();
        abort_if(!$customer, 404, 'Profil client non trouvé.');
        return $customer;
    }

    private function resolveCustomer(): Customer
    {
        $user = auth()->user();
        return Customer::firstOrCreate(
            ['user_id' => $user->id],
            [
                'type'       => Customer::TYPE_COMPANY,
                'company_id' => $user->company_id,
                'first_name' => explode(' ', $user->name)[0] ?? $user->name,
                'last_name'  => implode(' ', array_slice(explode(' ', $user->name), 1)) ?: '',
                'email'      => $user->email,
                'is_active'  => true,
            ]
        );
    }
}
