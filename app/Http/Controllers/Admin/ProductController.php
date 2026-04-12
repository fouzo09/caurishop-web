<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Requests\Admin\CreateVariantRequest;
use App\Http\Requests\Admin\UpdateVariantRequest;
use App\Services\Admin\ProductService;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:products.view', only: ['index', 'show']),
            new Middleware('permission:products.create', only: ['create', 'store', 'createVariant', 'storeVariant']),
            new Middleware('permission:products.edit', only: ['edit', 'update', 'editVariant', 'updateVariant']),
            new Middleware('permission:products.delete', only: ['destroy', 'destroyVariant']),
            new Middleware('permission:products.publish', only: ['publish', 'unpublish', 'activate', 'deactivate']),
        ];
    }

    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->get('search'),
            'is_active' => $request->get('is_active'),
            'is_published' => $request->get('is_published'),
            'type' => $request->get('type'),
            'credit_enabled' => $request->get('credit_enabled'),
        ];

        $products = $this->productService->getPaginatedProducts(15, $filters);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function store(CreateProductRequest $request): RedirectResponse
    {
        try {
            $product = $this->productService->createProduct($request->validated());

            $this->handleImageUploads($request, $product);

            if ($product->isVariable()) {
                return redirect()->route('admin.products.show', $product)
                    ->with('success', 'Produit variable créé. Ajoutez maintenant ses variantes.');
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Produit créé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création du produit: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Product $product): View
    {
        $product->load(['variants', 'images']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        try {
            $this->productService->updateProduct($product, $request->validated());

            $this->handleImageUploads($request, $product);

            return redirect()->route('admin.products.show', $product)
                ->with('success', 'Produit mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function storeImages(Request $request, Product $product): RedirectResponse
    {
        $request->validate(['images' => 'required|array', 'images.*' => 'image|max:4096']);
        $this->handleImageUploads($request, $product);
        return back()->with('success', 'Images ajoutées.');
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);
        Storage::delete($image->path);
        $wasPrimary = $image->is_primary;
        $image->delete();

        if ($wasPrimary) {
            $product->images()->oldest()->first()?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Image supprimée.');
    }

    public function setPrimaryImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);
        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);
        return back()->with('success', 'Image principale définie.');
    }

    private function handleImageUploads(Request $request, Product $product): void
    {
        if (!$request->hasFile('images')) return;

        $hasExisting = $product->images()->exists();
        $sort = (int) $product->images()->max('sort_order') + 1;

        foreach ($request->file('images') as $i => $file) {
            $path = $file->store("products/{$product->id}", 'public');
            $isPrimary = !$hasExisting && $i === 0;

            $product->images()->create([
                'path'       => $path,
                'sort_order' => $sort++,
                'is_primary' => $isPrimary,
            ]);

            $hasExisting = true;
        }
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            $this->productService->deleteProduct($product);

            return redirect()->route('admin.products.index')
                ->with('success', 'Produit supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function activate(Product $product): RedirectResponse
    {
        try {
            $this->productService->activateProduct($product);

            return redirect()->back()
                ->with('success', 'Produit activé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function deactivate(Product $product): RedirectResponse
    {
        try {
            $this->productService->deactivateProduct($product);

            return redirect()->back()
                ->with('success', 'Produit désactivé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function publish(Product $product): RedirectResponse
    {
        try {
            $this->productService->publishProduct($product);

            return redirect()->back()
                ->with('success', 'Produit publié avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function unpublish(Product $product): RedirectResponse
    {
        try {
            $this->productService->unpublishProduct($product);

            return redirect()->back()
                ->with('success', 'Produit dépublié avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    // Variant methods
    public function createVariant(Product $product): View
    {
        return view('admin.products.variants.create', compact('product'));
    }

    public function storeVariant(CreateVariantRequest $request, Product $product): RedirectResponse
    {
        try {
            $this->productService->createVariant($product, $request->validated());

            return redirect()->route('admin.products.show', $product)
                ->with('success', 'Variante créée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de la variante: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function editVariant(Product $product, ProductVariant $variant): View
    {
        return view('admin.products.variants.edit', compact('product', 'variant'));
    }

    public function updateVariant(UpdateVariantRequest $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        try {
            $this->productService->updateVariant($variant, $request->validated());

            return redirect()->route('admin.products.show', $product)
                ->with('success', 'Variante mise à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroyVariant(Product $product, ProductVariant $variant): RedirectResponse
    {
        try {
            $this->productService->deleteVariant($variant);

            return redirect()->route('admin.products.show', $product)
                ->with('success', 'Variante supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
