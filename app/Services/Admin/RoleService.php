<?php

namespace App\Services\Admin;

use App\Repositories\Admin\RoleRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(
        protected RoleRepository $roleRepository
    ) {}

    public function getPaginatedRoles(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->roleRepository->paginate($perPage, $filters);
    }

    public function getAllRoles(): Collection
    {
        return $this->roleRepository->all();
    }

    public function getRoleById(int $id): ?Role
    {
        return $this->roleRepository->findById($id);
    }

    public function createRole(array $data): Role
    {
        $role = $this->roleRepository->create($data);

        if (!empty($data['permissions'])) {
            $this->roleRepository->syncPermissions($role, $data['permissions']);
        }

        return $role->load('permissions');
    }

    public function updateRole(Role $role, array $data): Role
    {
        $this->roleRepository->update($role, $data);

        $this->roleRepository->syncPermissions($role, $data['permissions'] ?? []);

        return $role->fresh()->load('permissions');
    }

    public function deleteRole(Role $role): bool
    {
        if (in_array($role->name, ['admin', 'employee'])) {
            throw new \Exception('Le rôle "' . $role->name . '" est un rôle système et ne peut pas être supprimé.');
        }

        if ($role->users()->count() > 0) {
            throw new \Exception('Ce rôle est assigné à ' . $role->users()->count() . ' utilisateur(s) et ne peut pas être supprimé.');
        }

        return $this->roleRepository->delete($role);
    }

    public function getAllPermissions(): Collection
    {
        return $this->roleRepository->getAllPermissions();
    }

    public function getPermissionsGrouped(): array
    {
        $permissions = $this->getAllPermissions();
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $module = $parts[0] ?? 'other';
            $grouped[$module][] = $permission;
        }

        ksort($grouped);

        return $grouped;
    }
}
