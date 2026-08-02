<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $customer = $this->customer();

        $orders = $customer
            ? $customer->orders()->with('items')->latest('id')->take(5)->get()
            : collect();

        return view('shop.account.index', [
            'customer'        => $customer,
            'recentOrders'    => $orders,
            'ordersCount'     => $customer ? $customer->orders()->count() : 0,
            'favoritesCount'  => $customer ? $customer->favorites()->count() : 0,
            'defaultAddress'  => $customer?->addresses()->where('is_default', true)->first(),
        ]);
    }

    public function orders(): View
    {
        $customer = $this->customer();

        $orders = $customer
            ? $customer->orders()->with('items')->latest('id')->paginate(10)
            : Order::whereRaw('1 = 0')->paginate(10);

        return view('shop.account.orders', compact('orders', 'customer'));
    }

    public function showOrder(Order $order): View
    {
        $this->authorizeOrder($order);
        $order->load(['items.product', 'items.variant']);

        return view('shop.account.order-show', compact('order'));
    }

    public function profile(): View
    {
        return view('shop.account.profile', ['customer' => $this->customer()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user     = Auth::user();
        $customer = $this->customer();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'phone'      => ['required', 'string', 'max:30'],
            'email'      => ['nullable', 'email:rfc', 'unique:users,email,' . $user->id],
            'address'    => ['nullable', 'string', 'max:255'],
        ]);

        $customer?->update([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'phone'      => $data['phone'],
            'email'      => $data['email'],
            'address'    => $data['address'] ?? null,
        ]);

        $user->update([
            'name'  => trim($data['first_name'] . ' ' . $data['last_name']),
            'email' => $data['email'] ?: $user->email,
        ]);

        return redirect()->route('shop.account.profile')->with('success', 'Profil mis à jour.');
    }

    private function customer(): ?Customer
    {
        return Auth::user()->customer;
    }

    private function authorizeOrder(Order $order): void
    {
        $customer = $this->customer();
        abort_unless($customer && $order->customer_id === $customer->id, 403);
    }
}
