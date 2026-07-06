<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $companyA = Company::where('raison_sociale', 'LIKE', '%Entreprise A%')->firstOrFail();
        $companyB = Company::where('raison_sociale', 'LIKE', '%Entreprise B%')->firstOrFail();

        $accounts = [
            // ── Super Admin plateforme ──────────────────────────────────
            [
                'name'       => 'CAURISHOP Admin',
                'email'      => 'admin@caurishop.test',
                'password'   => 'password',
                'company_id' => null,
                'role'       => 'admin',
            ],

            // ── Admin Entreprise A ──────────────────────────────────────
            [
                'name'       => 'Fatoumata Camara',
                'email'      => 'admin@entreprise-a.test',
                'password'   => 'password',
                'company_id' => $companyA->id,
                'role'       => 'company_admin',
            ],

            // ── Employés Entreprise A ───────────────────────────────────
            [
                'name'       => 'Ibrahima Bah',
                'email'      => 'ibrahima@entreprise-a.test',
                'password'   => 'password',
                'company_id' => $companyA->id,
                'role'       => 'company_employee',
            ],
            [
                'name'       => 'Mariama Sow',
                'email'      => 'mariama@entreprise-a.test',
                'password'   => 'password',
                'company_id' => $companyA->id,
                'role'       => 'company_employee',
            ],

            // ── Admin Entreprise B ──────────────────────────────────────
            [
                'name'       => 'Oumar Diallo',
                'email'      => 'admin@entreprise-b.test',
                'password'   => 'password',
                'company_id' => $companyB->id,
                'role'       => 'company_admin',
            ],

            // ── Employé Entreprise B ────────────────────────────────────
            [
                'name'       => 'Aissatou Barry',
                'email'      => 'aissatou@entreprise-b.test',
                'password'   => 'password',
                'company_id' => $companyB->id,
                'role'       => 'company_employee',
            ],
        ];

        foreach ($accounts as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'       => $data['name'],
                    'password'   => Hash::make($data['password']),
                    'company_id' => $data['company_id'],
                    'is_active'  => true,
                ]
            );

            // Sync role (remove old, assign new)
            $user->syncRoles([$data['role']]);

            $this->command->line("  <info>✓</info> {$data['role']}: {$data['email']}");
        }
    }
}
