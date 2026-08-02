<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Favoris de l'espace client.
 */
class FavoriteController extends Controller
{
    public function index(): View
    {
        $customer = $this->customer();

        $products = $customer
            ? $customer->favoriteProducts()
                ->with(['images', 'variants', 'category'])
                ->orderByDesc('favorites.id')
                ->get()
            : collect();

        return view('shop.account.favorites', compact('customer', 'products'));
    }

    /**
     * Bascule un produit en favori. Répond en JSON pour la carte produit,
     * en redirection pour un POST classique (formulaire sans JS).
     */
    public function toggle(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $customer = $this->customer();
        abort_unless($customer, 403);

        $existing = $customer->favorites()->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
        } else {
            $customer->favorites()->create(['product_id' => $product->id]);
            $favorited = true;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'favorited' => $favorited,
                'count'     => $customer->favorites()->count(),
            ]);
        }

        return back()->with('success', $favorited ? 'Ajouté à vos favoris.' : 'Retiré de vos favoris.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $customer = $this->customer();
        abort_unless($customer, 403);

        $customer->favorites()->where('product_id', $product->id)->delete();

        return redirect()->route('shop.account.favorites')->with('success', 'Retiré de vos favoris.');
    }

    private function customer(): ?Customer
    {
        return Auth::user()->customer;
    }
}
