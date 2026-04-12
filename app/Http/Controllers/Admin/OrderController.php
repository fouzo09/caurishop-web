<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateOrderRequest;
use App\Services\Admin\OrderService;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class OrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:orders.view', only: ['index', 'show']),
            new Middleware('permission:orders.create', only: ['create', 'store']),
            new Middleware('permission:orders.confirm', only: ['confirm']),
            new Middleware('permission:orders.deliver', only: ['deliver']),
            new Middleware('permission:orders.cancel', only: ['cancel']),
        ];
    }

    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search'      => $request->get('search'),
            'status'      => $request->get('status'),
            'order_type'  => $request->get('order_type'),
            'customer_id' => $request->get('customer_id'),
        ];

        $orders    = $this->orderService->getPaginatedOrders(15, $filters);
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();

        return view('admin.orders.index', compact('orders', 'customers'));
    }

    public function create(): View
    {
        $customers = Customer::with('company')
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $products = Product::with('variants')
            ->where('is_active', true)
            ->where('is_published', true)
            ->orderBy('name')
            ->get();

        $productsJson = $products->map(function ($p) {
            return [
                'id'       => $p->id,
                'name'     => $p->name,
                'sku'      => $p->sku,
                'type'     => $p->type,
                'price'    => (float) $p->price,
                'variants' => $p->variants->map(function ($v) {
                    return [
                        'id'    => $v->id,
                        'name'  => $v->name,
                        'sku'   => $v->sku,
                        'price' => (float) $v->price,
                    ];
                })->values(),
            ];
        })->values()->toJson();

        return view('admin.orders.create', compact('customers', 'products', 'productsJson'));
    }

    public function store(CreateOrderRequest $request): RedirectResponse
    {
        try {
            $order = $this->orderService->createOrder($request->validated());

            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Commande créée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function show(Order $order): View
    {
        $order->load(['customer.company', 'items.product', 'items.variant', 'creditPlan.installments', 'creator']);

        return view('admin.orders.show', compact('order'));
    }

    public function confirm(Order $order): RedirectResponse
    {
        try {
            $this->orderService->confirmOrder($order);

            return redirect()->back()
                ->with('success', 'Commande confirmée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function deliver(Order $order): RedirectResponse
    {
        try {
            $this->orderService->deliverOrder($order);

            return redirect()->back()
                ->with('success', 'Commande marquée comme livrée. Stock mis à jour.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function cancel(Order $order): RedirectResponse
    {
        try {
            $this->orderService->cancelOrder($order);

            return redirect()->back()
                ->with('success', 'Commande annulée.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
