<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);

        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@caurishop.test'],
            [
                'name' => 'CAURISHOP Admin',
                'password' => Hash::make('password'),
            ]
        );

        if (!$admin->hasRole($adminRole)) {
            $admin->assignRole($adminRole);
        }

        // Employee user
        $employee = User::firstOrCreate(
            ['email' => 'employee@caurishop.test'],
            [
                'name' => 'CAURISHOP Employee',
                'password' => Hash::make('password'),
            ]
        );

        if (!$employee->hasRole($employeeRole)) {
            $employee->assignRole($employeeRole);
        }
    }
}
