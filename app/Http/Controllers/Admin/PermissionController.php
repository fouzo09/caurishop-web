<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RoleService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:permissions.view', only: ['index']),
        ];
    }

    public function __construct(
        protected RoleService $roleService
    ) {}

    public function index(): View
    {
        $permissionsGrouped = $this->roleService->getPermissionsGrouped();
        $total = $this->roleService->getAllPermissions()->count();

        return view('admin.permissions.index', compact('permissionsGrouped', 'total'));
    }
}
