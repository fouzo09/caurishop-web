<?php

namespace App\Services\Admin;

use App\Models\PlatformMargin;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class MarginService
{
    /**
     * Trouve la marge applicable pour un produit (spécifique en priorité, globale en fallback).
     */
    public function findMarginForProduct(?int $productId): ?PlatformMargin
    {
        if ($productId) {
            $specific = PlatformMargin::active()
                ->forProduct($productId)
                ->first();

            if ($specific) {
                return $specific;
            }
        }

        return PlatformMargin::active()->global()->first();
    }

    /**
     * Calcule le prix de vente client à partir du prix fournisseur.
     */
    public function calculateSalePrice(float $supplierPrice, ?int $productId = null): float
    {
        $margin = $this->findMarginForProduct($productId);

        if (!$margin) {
            return $supplierPrice;
        }

        return $margin->apply($supplierPrice);
    }

    /**
     * Si le produit a un supplier_price, recalcule son price selon la marge.
     */
    public function applyToProduct(Product $product): void
    {
        if (!$product->supplier_price) {
            return;
        }

        $salePrice = $this->calculateSalePrice((float) $product->supplier_price, $product->id);
        $product->updateQuietly(['price' => $salePrice]);
    }

    /**
     * Si la variante a un supplier_price, recalcule son price selon la marge du produit parent.
     */
    public function applyToVariant(ProductVariant $variant): void
    {
        if (!$variant->supplier_price) {
            return;
        }

        $salePrice = $this->calculateSalePrice(
            (float) $variant->supplier_price,
            $variant->product_id
        );
        $variant->updateQuietly(['price' => $salePrice]);
    }

    /**
     * Recalcule les prix de tous les produits et variantes ayant un supplier_price.
     */
    public function recalculateAll(): array
    {
        $updated = ['products' => 0, 'variants' => 0];

        Product::whereNotNull('supplier_price')->each(function (Product $product) use (&$updated) {
            $this->applyToProduct($product);
            $updated['products']++;
        });

        ProductVariant::whereNotNull('supplier_price')->each(function (ProductVariant $variant) use (&$updated) {
            $this->applyToVariant($variant);
            $updated['variants']++;
        });

        return $updated;
    }

    // ─── CRUD marges ──────────────────────────────────────────────────

    public function getGlobalMargin(): ?PlatformMargin
    {
        return PlatformMargin::global()->first();
    }

    public function getProductMargins(): \Illuminate\Database\Eloquent\Collection
    {
        return PlatformMargin::where('scope', PlatformMargin::SCOPE_PRODUCT)
            ->with([])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createMargin(array $data): PlatformMargin
    {
        return PlatformMargin::create($data);
    }

    public function updateMargin(PlatformMargin $margin, array $data): PlatformMargin
    {
        $margin->update($data);
        return $margin->fresh();
    }

    public function deleteMargin(PlatformMargin $margin): void
    {
        $margin->delete();
    }

    public function toggleMargin(PlatformMargin $margin): PlatformMargin
    {
        $margin->update(['is_active' => !$margin->is_active]);
        return $margin->fresh();
    }
}
