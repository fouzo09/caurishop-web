<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::where('is_published', true)->where('is_active', true)->with('variants');

        if ($search = $request->get('search')) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        $products = $query->with('images')->inRandomOrder()->paginate(12);

        return view('portal.products.index', compact('products'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_published && $product->is_active, 404);
        $product->load(['variants', 'images']);

        return view('portal.products.show', compact('product'));
    }
}
