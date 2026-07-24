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

        // Meilleures ventes : produits publiés les plus commandés.
        $bestSellers = Product::query()
            ->published()
            ->withCount('orderItems')
            ->with(['images', 'variants'])
            ->orderByDesc('order_items_count')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        // Nouveautés : derniers produits publiés.
        $newArrivals = Product::query()
            ->published()
            ->with(['images', 'variants'])
            ->latest('id')
            ->take(5)
            ->get();

        return view('shop.home', compact('categories', 'bestSellers', 'newArrivals'));
    }
}
