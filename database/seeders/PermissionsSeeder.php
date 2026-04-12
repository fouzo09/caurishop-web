<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.suspend',
            // Companies
            'companies.view',
            'companies.create',
            'companies.edit',
            'companies.delete',
            'companies.activate',
            // Customers
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
            'customers.activate',
            // Products
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'products.publish',
            // Orders
            'orders.view',
            'orders.create',
            'orders.confirm',
            'orders.deliver',
            'orders.cancel',
            // Payments & Installments
            'payments.view',
            'installments.view',
            'installments.pay',
            // Roles & Permissions
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'permissions.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Admin gets all permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        // Employee gets limited permissions
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->syncPermissions([
            'users.view',
            'companies.view',
            'customers.view',
            'customers.create',
            'customers.edit',
            'products.view',
            'orders.view',
            'orders.create',
            'orders.confirm',
            'orders.deliver',
            'orders.cancel',
            'payments.view',
            'installments.view',
            'installments.pay',
        ]);
    }
}
