<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Services\Admin\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:roles.view', only: ['index', 'show']),
            new Middleware('permission:roles.create', only: ['create', 'store']),
            new Middleware('permission:roles.edit', only: ['edit', 'update']),
            new Middleware('permission:roles.delete', only: ['destroy']),
        ];
    }

    public function __construct(
        protected RoleService $roleService
    ) {}

    public function index(Request $request): View
    {
        $filters = ['search' => $request->get('search')];
        $roles = $this->roleService->getPaginatedRoles(15, $filters);

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissionsGrouped = $this->roleService->getPermissionsGrouped();

        return view('admin.roles.create', compact('permissionsGrouped'));
    }

    public function store(CreateRoleRequest $request): RedirectResponse
    {
        try {
            $this->roleService->createRole($request->validated());

            return redirect()->route('admin.roles.index')
                ->with('success', 'Rôle créé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création du rôle : ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Role $role): View
    {
        $role->load(['permissions', 'users']);

        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');
        $permissionsGrouped = $this->roleService->getPermissionsGrouped();

        return view('admin.roles.edit', compact('role', 'permissionsGrouped'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        try {
            $this->roleService->updateRole($role, $request->validated());

            return redirect()->route('admin.roles.index')
                ->with('success', 'Rôle mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Role $role): RedirectResponse
    {
        try {
            $this->roleService->deleteRole($role);

            return redirect()->route('admin.roles.index')
                ->with('success', 'Rôle supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
