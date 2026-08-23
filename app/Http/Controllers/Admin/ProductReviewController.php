<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\Admin\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

/**
 * Modération des avis clients d'un produit : consultation, masquage, suppression.
 */
class ProductReviewController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:products.view', only: ['index']),
            new Middleware('permission:products.edit', only: ['toggle']),
            new Middleware('permission:products.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request, Product $product): View
    {
        $query = $product->reviews()->with('customer');

        // Filtres : statut de publication, note, recherche dans le commentaire.
        if ($request->filled('status')) {
            $query->where('is_approved', $request->get('status') === 'published');
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->get('rating'));
        }

        if ($request->filled('search')) {
            // Recherche insensible à la casse et portable (PostgreSQL + SQLite).
            $term = '%' . $request->get('search') . '%';
            $query->where(fn ($q) => $q
                ->whereRaw('LOWER(comment) LIKE LOWER(?)', [$term])
                ->orWhereRaw('LOWER(title) LIKE LOWER(?)', [$term]));
        }

        $reviews = $query->latest('id')->paginate(20);

        // Compteurs sur l'ensemble des avis du produit, pas seulement la page.
        $all = $product->reviews();

        return view('admin.products.reviews', [
            'product'   => $product,
            'reviews'   => $reviews,
            'total'     => (clone $all)->count(),
            'published' => (clone $all)->where('is_approved', true)->count(),
            'hidden'    => (clone $all)->where('is_approved', false)->count(),
            'average'   => round((float) (clone $all)->where('is_approved', true)->avg('rating'), 1),
        ]);
    }

    /** Masque un avis du site public, ou le republie. */
    public function toggle(Product $product, ProductReview $review): RedirectResponse
    {
        $this->authorizeReview($product, $review);

        $review->update(['is_approved' => ! $review->is_approved]);

        app(ActivityLogService::class)->log(
            $review->is_approved ? 'review_published' : 'review_hidden',
            ($review->is_approved ? 'Avis republié' : 'Avis masqué') . " — produit « {$product->name} » (avis #{$review->id})",
            null,
            ['product_id' => $product->id, 'review_id' => $review->id],
        );

        return back()->with('success', $review->is_approved ? 'Avis republié.' : 'Avis masqué du site.');
    }

    public function destroy(Product $product, ProductReview $review): RedirectResponse
    {
        $this->authorizeReview($product, $review);

        $author = $review->authorName();
        $review->delete();

        app(ActivityLogService::class)->log(
            'review_deleted',
            "Avis supprimé — produit « {$product->name} », client {$author}",
            null,
            ['product_id' => $product->id, 'review_id' => $review->id],
        );

        return back()->with('success', 'Avis supprimé.');
    }

    /** L'avis doit bien appartenir au produit de l'URL. */
    private function authorizeReview(Product $product, ProductReview $review): void
    {
        abort_unless($review->product_id === $product->id, 404);
    }
}
