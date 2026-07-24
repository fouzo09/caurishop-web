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
        // Rôles plateforme
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Rôles entreprise (legacy conservé)
        Role::firstOrCreate(['name' => 'employee']);

        // Nouveaux rôles
        Role::firstOrCreate(['name' => 'company_admin']);
        Role::firstOrCreate(['name' => 'company_employee']);

        // Client public (parcours e-commerce grand public) — aucune permission admin
        Role::firstOrCreate(['name' => 'customer']);

        // Super Admin CauriShop
        $admin = User::firstOrCreate(
            ['email' => 'admin@caurishop.test'],
            [
                'name'     => 'CAURISHOP Admin',
                'password' => Hash::make('password'),
            ]
        );

        if (!$admin->hasRole($adminRole)) {
            $admin->assignRole($adminRole);
        }
    }
}
