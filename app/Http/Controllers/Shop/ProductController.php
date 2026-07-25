<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::active()->orderBy('sort_order')->orderBy('name')->get();

        $activeCategory = $request->filled('category')
            ? $categories->firstWhere('slug', $request->string('category')->value())
            : null;

        $query = Product::query()
            ->published()
            ->with(['images', 'variants']);

        if ($activeCategory) {
            $query->where('category_id', $activeCategory->id);
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->value();
            // Recherche insensible à la casse, portable (PostgreSQL + SQLite).
            $query->whereRaw('LOWER(name) LIKE LOWER(?)', ['%' . $term . '%']);
        }

        // Prix max réel du catalogue publié (borne du curseur).
        $maxPrice = (int) ceil((float) (Product::published()->max('price') ?? 0));
        $maxPrice = max($maxPrice, 1);

        // On ne filtre que si l'utilisateur a réellement abaissé la borne sous le max.
        if ($request->filled('max_price') && (float) $request->input('max_price') < $maxPrice) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }

        // Tri
        $sort = $request->string('sort')->value() ?: 'popularity';
        match ($sort) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'newest'     => $query->latest('id'),
            default      => $query->withCount('orderItems')->orderByDesc('order_items_count')->orderByDesc('id'),
        };

        $products = $query->paginate(12)->withQueryString();

        return view('shop.products.index', [
            'products'       => $products,
            'categories'     => $categories,
            'activeCategory' => $activeCategory,
            'sort'           => $sort,
            'maxPrice'       => $maxPrice,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_published && $product->is_active, 404);

        $product->load(['images', 'variants' => fn ($q) => $q->where('is_active', true), 'category']);

        // Suggestions : même catégorie, produits publiés différents.
        $suggestions = Product::query()
            ->published()
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->with(['images', 'variants'])
            ->latest('id')
            ->take(4)
            ->get();

        return view('shop.products.show', compact('product', 'suggestions'));
    }
}
