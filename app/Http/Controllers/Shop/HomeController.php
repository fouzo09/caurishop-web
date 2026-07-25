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

        // Nouveautés : derniers produits publiés (3 rangées de 5).
        $newArrivals = Product::query()
            ->published()
            ->with(['images', 'variants'])
            ->latest('id')
            ->take(15)
            ->get();

        return view('shop.home', compact('categories', 'newArrivals'));
    }
}
