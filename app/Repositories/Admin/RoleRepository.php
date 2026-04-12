<?php

namespace App\Repositories\Admin;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleRepository
{
    public function __construct(
        protected Role $model
    ) {}

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->withCount('users')->with('permissions');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model->with('permissions')->get();
    }

    public function findById(int $id): ?Role
    {
        return $this->model->with(['permissions', 'users'])->find($id);
    }

    public function create(array $data): Role
    {
        return $this->model->create(['name' => $data['name']]);
    }

    public function update(Role $role, array $data): bool
    {
        return $role->update(['name' => $data['name']]);
    }

    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $permissions = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
        $role->syncPermissions($permissions);
    }

    public function getAllPermissions(): Collection
    {
        return Permission::orderBy('name')->get();
    }
}
