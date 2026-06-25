<?php

namespace App\Services\Admin;

use App\Repositories\Admin\ProductRepository;
use App\Repositories\Admin\ProductVariantRepository;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Admin\MarginService;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductVariantRepository $variantRepository,
        protected MarginService $marginService
    ) {}

    public function getPaginatedProducts(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->productRepository->paginate($perPage, $filters);
    }

    public function getAllProducts(): Collection
    {
        return $this->productRepository->all();
    }

    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    public function createProduct(array $data): Product
    {
        DB::beginTransaction();

        try {
            // Générer le slug si non fourni
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $data['is_active'] = $data['is_active'] ?? true;
            $data['is_published'] = $data['is_published'] ?? false;
            $data['credit_enabled'] = $data['credit_enabled'] ?? false;

            // Calcul automatique du prix si supplier_price fourni (product ID pas encore connu, on utilisera la marge globale)
            if (!empty($data['supplier_price'])) {
                $data['price'] = $this->marginService->calculateSalePrice((float) $data['supplier_price']);
            }

            $product = $this->productRepository->create($data);

            // Re-calcul avec l'ID du produit maintenant connu (marge spécifique produit possible)
            if ($product->supplier_price) {
                $this->marginService->applyToProduct($product);
                $product->refresh();
            }

            // Si produit variable, créer les variantes
            if ($product->type === Product::TYPE_VARIABLE && !empty($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $variantData['product_id'] = $product->id;
                    $this->variantRepository->create($variantData);
                }
            }

            DB::commit();

            return $product->fresh(['variants']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateProduct(Product $product, array $data): Product
    {
        DB::beginTransaction();

        try {
            // Générer le slug si modifié
            if (!empty($data['name']) && $data['name'] !== $product->name) {
                $data['slug'] = Str::slug($data['name']);
            }

            $this->productRepository->update($product, $data);

            // Recalcul du prix si supplier_price présent
            if (!empty($data['supplier_price'])) {
                $product->refresh();
                $this->marginService->applyToProduct($product);
            }

            DB::commit();

            return $product->fresh(['variants']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteProduct(Product $product): bool
    {
        // Vérifier s'il y a des commandes
        if ($product->orderItems()->count() > 0) {
            throw new \Exception('Impossible de supprimer un produit avec des commandes associées.');
        }

        DB::beginTransaction();

        try {
            // Supprimer les variantes
            $product->variants()->delete();

            // Supprimer le produit
            $result = $this->productRepository->delete($product);

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function activateProduct(Product $product): bool
    {
        if ($product->is_active) {
            throw new \Exception('Le produit est déjà actif.');
        }

        return $this->productRepository->activate($product);
    }

    public function deactivateProduct(Product $product): bool
    {
        if (!$product->is_active) {
            throw new \Exception('Le produit est déjà désactivé.');
        }

        return $this->productRepository->deactivate($product);
    }

    public function publishProduct(Product $product): bool
    {
        if ($product->is_published) {
            throw new \Exception('Le produit est déjà publié.');
        }

        if ($product->isVariable() && $product->variants()->where('is_active', true)->count() === 0) {
            throw new \Exception('Impossible de publier un produit variable sans variantes actives.');
        }

        return $this->productRepository->publish($product);
    }

    public function unpublishProduct(Product $product): bool
    {
        if (!$product->is_published) {
            throw new \Exception('Le produit est déjà dépublié.');
        }

        return $this->productRepository->unpublish($product);
    }

    public function skuExists(string $sku, ?int $excludeProductId = null): bool
    {
        $product = $this->productRepository->findBySku($sku);

        if (!$product) {
            return false;
        }

        return $excludeProductId ? $product->id !== $excludeProductId : true;
    }

    // Variant methods
    public function createVariant(Product $product, array $data): ProductVariant
    {
        if ($product->type !== Product::TYPE_VARIABLE) {
            throw new \Exception('Les variantes ne peuvent être ajoutées qu\'aux produits variables.');
        }

        $data['product_id'] = $product->id;

        if (!empty($data['supplier_price'])) {
            $data['price'] = $this->marginService->calculateSalePrice(
                (float) $data['supplier_price'],
                $product->id
            );
        }

        $variant = $this->variantRepository->create($data);

        if ($variant->supplier_price) {
            $this->marginService->applyToVariant($variant);
            $variant->refresh();
        }

        return $variant;
    }

    public function updateVariant(ProductVariant $variant, array $data): ProductVariant
    {
        $this->variantRepository->update($variant, $data);

        if (!empty($data['supplier_price'])) {
            $variant->refresh();
            $this->marginService->applyToVariant($variant);
        }

        return $variant->fresh();
    }

    public function deleteVariant(ProductVariant $variant): bool
    {
        return $this->variantRepository->delete($variant);
    }

    public function activateVariant(ProductVariant $variant): bool
    {
        return $this->variantRepository->activate($variant);
    }

    public function deactivateVariant(ProductVariant $variant): bool
    {
        return $this->variantRepository->deactivate($variant);
    }
}
