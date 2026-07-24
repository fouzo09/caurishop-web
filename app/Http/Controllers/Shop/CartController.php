<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Shop\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(): View
    {
        return view('shop.cart', ['summary' => $this->cart->summary()]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity'   => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        // Sécurité : on n'ajoute que des produits publiés/actifs.
        $product = Product::published()->findOr($data['product_id'], fn () => abort(404));

        $this->cart->add(
            $product->id,
            $data['variant_id'] ?? null,
            $data['quantity'] ?? 1,
        );

        return redirect()->route('shop.cart.index')->with('success', 'Produit ajouté au panier.');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'variant_id' => ['nullable', 'integer'],
            'quantity'   => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->cart->update($data['product_id'], $data['variant_id'] ?? null, $data['quantity']);

        return redirect()->route('shop.cart.index');
    }

    public function remove(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'variant_id' => ['nullable', 'integer'],
        ]);

        $this->cart->remove($data['product_id'], $data['variant_id'] ?? null);

        return redirect()->route('shop.cart.index')->with('success', 'Produit retiré du panier.');
    }

    public function applyPromo(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['nullable', 'string', 'max:40']]);

        if (empty($data['code'])) {
            $this->cart->clearPromo();
            return redirect()->route('shop.cart.index');
        }

        if ($this->cart->applyPromo($data['code'])) {
            return redirect()->route('shop.cart.index')->with('success', 'Code promo appliqué.');
        }

        return redirect()->route('shop.cart.index')->withErrors(['code' => 'Code promo invalide.']);
    }
}
