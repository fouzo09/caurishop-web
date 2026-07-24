<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Payments\PaymentManager;
use App\Payments\PaymentResult;
use App\Services\Admin\OrderService;
use App\Services\Shop\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /** Modes de livraison disponibles (Phase 1). */
    private const DELIVERY = [
        'standard' => ['label' => 'Livraison standard', 'fee' => 0],
        'express'  => ['label' => 'Livraison express', 'fee' => 50000],
    ];

    public function __construct(
        private readonly CartService $cart,
        private readonly PaymentManager $payments,
    ) {
    }

    public function index(): View|RedirectResponse
    {
        $summary = $this->cart->summary();

        if (empty($summary['items'])) {
            return redirect()->route('shop.cart.index')->with('success', 'Votre panier est vide.');
        }

        $customer = $this->currentCustomer();

        return view('shop.checkout', [
            'summary'   => $summary,
            'customer'  => $customer,
            'methods'   => $this->payments->available($customer),
            'deliveries' => self::DELIVERY,
        ]);
    }

    public function store(Request $request, OrderService $orders): RedirectResponse
    {
        $summary = $this->cart->summary();

        if (empty($summary['items'])) {
            return redirect()->route('shop.cart.index')->with('success', 'Votre panier est vide.');
        }

        $customer = $this->currentCustomer();

        $data = $request->validate([
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'phone'           => ['required', 'string', 'max:30'],
            'address'         => ['required', 'string', 'max:255'],
            'city'            => ['required', 'string', 'max:100'],
            'delivery_method' => ['required', 'string', 'in:' . implode(',', array_keys(self::DELIVERY))],
            'payment_method'  => ['required', 'string'],
            'payment_phone'   => ['nullable', 'string', 'max:30'],
        ]);

        $provider = $this->payments->get($data['payment_method']);
        if (! $provider || ! $provider->isAvailableFor($customer)) {
            return back()->withErrors(['payment_method' => 'Moyen de paiement invalide.'])->withInput();
        }

        if ($provider->requiresPhone() && empty($data['payment_phone'])) {
            return back()->withErrors(['payment_phone' => 'Numéro Mobile Money requis pour ce moyen de paiement.'])->withInput();
        }

        // 1) Création de la commande via le service existant (réutilisation).
        $order = $orders->createOrder([
            'items' => array_map(fn ($item) => [
                'product_id' => $item['product']->id,
                'variant_id' => $item['variant']?->id,
                'quantity'   => $item['quantity'],
            ], $summary['items']),
            'customer_id' => $customer->id,
            'order_type'  => Order::TYPE_CASH,
        ]);

        // 2) Renseignement des infos livraison / paiement (colonnes additives).
        $deliveryFee = self::DELIVERY[$data['delivery_method']]['fee'];

        $order->update([
            'shipping_name'    => trim($data['first_name'] . ' ' . $data['last_name']),
            'shipping_phone'   => $data['phone'],
            'shipping_address' => $data['address'],
            'shipping_city'    => $data['city'],
            'delivery_method'  => $data['delivery_method'],
            'delivery_fee'     => $deliveryFee,
            'discount_amount'  => $summary['discount'],
            'payment_method'   => $provider->key(),
        ]);

        // 3) Paiement (simulé) + finalisation.
        $result = $provider->process($order, $data);

        $order->update([
            'payment_status'    => $result->status,
            'payment_reference' => $result->reference,
        ]);

        if ($result->isPaid()) {
            // Commande réglée → confirmée (cash : pas de plan de crédit).
            $orders->confirmOrder($order);
        } else {
            $order->update(['status' => Order::STATUS_PENDING_PAYMENT]);
        }

        // 4) Vidage du panier.
        $this->cart->clear();
        $this->cart->clearPromo();

        return redirect()->route('shop.checkout.confirmation', $order->id);
    }

    public function confirmation(Order $order): View
    {
        $this->authorizeOrder($order);

        $order->load(['items.product', 'items.variant', 'customer']);

        return view('shop.confirmation', compact('order'));
    }

    /**
     * Récupère le Customer du user connecté, en le créant si nécessaire
     * (cas d'un compte sans profil client encore rattaché).
     */
    private function currentCustomer(): Customer
    {
        $user = Auth::user();

        return $user->customer ?? Customer::create([
            'user_id'    => $user->id,
            'type'       => Customer::TYPE_INDIVIDUAL,
            'company_id' => null,
            'first_name' => $user->name,
            'last_name'  => '',
            'email'      => $user->email,
            'is_active'  => true,
        ]);
    }

    private function authorizeOrder(Order $order): void
    {
        $customer = Auth::user()->customer;
        abort_unless($customer && $order->customer_id === $customer->id, 403);
    }
}
