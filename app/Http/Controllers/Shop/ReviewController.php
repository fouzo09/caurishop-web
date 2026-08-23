<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Avis et commentaires clients sur une fiche produit.
 * Un client ne laisse qu'un avis par produit : le réenvoi du formulaire
 * met à jour l'avis existant plutôt que d'en empiler un second.
 */
class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_published && $product->is_active, 404);

        $customer = $this->customer();

        if (! $customer) {
            return back()->with('error', "Votre compte client n'est pas encore actif.");
        }

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'between:1,5'],
            'title'   => ['nullable', 'string', 'max:120'],
            'comment' => ['required', 'string', 'min:5', 'max:2000'],
        ], [], [
            'rating'  => 'note',
            'title'   => 'titre',
            'comment' => 'commentaire',
        ]);

        ProductReview::updateOrCreate(
            ['product_id' => $product->id, 'customer_id' => $customer->id],
            $data + [
                'is_verified' => $this->hasPurchased($customer, $product),
                'is_approved' => true,
            ],
        );

        return redirect()->to(route('shop.products.show', $product) . '#avis')
            ->with('success', 'Merci, votre avis est publié.');
    }

    public function destroy(ProductReview $review): RedirectResponse
    {
        $customer = $this->customer();
        abort_unless($customer && $review->customer_id === $customer->id, 403);

        $productId = $review->product_id;
        $review->delete();

        return redirect()->to(route('shop.products.show', $productId) . '#avis')
            ->with('success', 'Votre avis a été supprimé.');
    }

    /** Achat vérifié : le client a déjà une commande non annulée contenant ce produit. */
    private function hasPurchased(Customer $customer, Product $product): bool
    {
        return OrderItem::where('product_id', $product->id)
            ->whereHas('order', fn ($q) => $q
                ->where('customer_id', $customer->id)
                ->where('status', '!=', Order::STATUS_CANCELLED))
            ->exists();
    }

    private function customer(): ?Customer
    {
        return Auth::user()?->customer;
    }
}
