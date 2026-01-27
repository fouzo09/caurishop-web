<?php

namespace App\Repositories\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function __construct(
        protected Product $model
    ) {}

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['variants']);

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('sku', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_published'])) {
            $query->where('is_published', $filters['is_published']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['credit_enabled'])) {
            $query->where('credit_enabled', $filters['credit_enabled']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model->with('variants')->get();
    }

    public function findById(int $id): ?Product
    {
        return $this->model->with(['variants'])->find($id);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->model->where('sku', $sku)->first();
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function activate(Product $product): bool
    {
        return $product->update(['is_active' => true]);
    }

    public function deactivate(Product $product): bool
    {
        return $product->update(['is_active' => false]);
    }

    public function publish(Product $product): bool
    {
        return $product->update(['is_published' => true]);
    }

    public function unpublish(Product $product): bool
    {
        return $product->update(['is_published' => false]);
    }

    public function getPublished(): Collection
    {
        return $this->model->where('is_published', true)
            ->where('is_active', true)
            ->get();
    }

    public function getSimple(): Collection
    {
        return $this->model->where('type', Product::TYPE_SIMPLE)->get();
    }

    public function getVariable(): Collection
    {
        return $this->model->where('type', Product::TYPE_VARIABLE)->get();
    }
}
