<?php

namespace App\Repositories\Admin;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection;

class ProductVariantRepository
{
    public function __construct(
        protected ProductVariant $model
    ) {}

    public function findById(int $id): ?ProductVariant
    {
        return $this->model->with('product')->find($id);
    }

    public function getByProduct(int $productId): Collection
    {
        return $this->model->where('product_id', $productId)->get();
    }

    public function create(array $data): ProductVariant
    {
        return $this->model->create($data);
    }

    public function update(ProductVariant $variant, array $data): bool
    {
        return $variant->update($data);
    }

    public function delete(ProductVariant $variant): bool
    {
        return $variant->delete();
    }

    public function activate(ProductVariant $variant): bool
    {
        return $variant->update(['is_active' => true]);
    }

    public function deactivate(ProductVariant $variant): bool
    {
        return $variant->update(['is_active' => false]);
    }
}
