<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Nouveautés : derniers produits publiés (carrousel horizontal).
        $newArrivals = Product::query()
            ->published()
            ->withRatings()
            ->with(['images', 'variants', 'category'])
            ->latest('id')
            ->take(10)
            ->get();

        // Nos produits : les plus commandés (grille).
        $popularProducts = Product::query()
            ->published()
            ->withRatings()
            ->with(['images', 'variants', 'category'])
            ->withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        return view('shop.home', compact('categories', 'newArrivals', 'popularProducts'));
    }
}
