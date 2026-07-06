<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Companies ────────────────────────────────────────────────
        DB::table('companies')->updateOrInsert(
            ['registration_number' => 'RCCM-0001'],
            [
                'raison_sociale'      => 'CAURISHOP Entreprise A',
                'email'               => 'contactA@company.test',
                'phone'               => '+224 000 000 001',
                'address'             => 'Conakry',
                'city'                => 'Conakry',
                'country'             => 'GN',
                'credit_limit'        => 5000000,
                'is_active'           => true,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]
        );
        $companyAId = DB::table('companies')->where('registration_number', 'RCCM-0001')->value('id');

        DB::table('companies')->updateOrInsert(
            ['registration_number' => 'RCCM-0002'],
            [
                'raison_sociale'      => 'CAURISHOP Entreprise B',
                'email'               => 'contactB@company.test',
                'phone'               => '+224 000 000 002',
                'address'             => 'Conakry',
                'city'                => 'Conakry',
                'country'             => 'GN',
                'credit_limit'        => 10000000,
                'is_active'           => true,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]
        );
        $companyBId = DB::table('companies')->where('registration_number', 'RCCM-0002')->value('id');

        // ── Customers ────────────────────────────────────────────────
        DB::table('customers')->updateOrInsert(
            ['email' => 'mamadou@client.test'],
            [
                'type'                 => 'individual',
                'company_id'           => null,
                'first_name'           => 'Mamadou',
                'last_name'            => 'Diallo',
                'company_contact_name' => null,
                'phone'                => '+224 600 000 000',
                'address'              => 'Conakry',
                'is_active'            => true,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]
        );

        DB::table('customers')->updateOrInsert(
            ['email' => 'achats@companyA.test'],
            [
                'type'                 => 'company',
                'company_id'           => $companyAId,
                'first_name'           => null,
                'last_name'            => null,
                'company_contact_name' => 'Responsable Achats',
                'phone'                => '+224 610 000 000',
                'address'              => 'Conakry',
                'is_active'            => true,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]
        );

        // ── Products ─────────────────────────────────────────────────
        DB::table('products')->updateOrInsert(
            ['sku' => 'LAPTOP-PRO-14'],
            [
                'type'                      => 'simple',
                'name'                      => 'Laptop Pro 14',
                'description'               => 'Ordinateur portable pour professionnels.',
                'price'                     => 12000000,
                'is_published'              => true,
                'is_active'                 => true,
                'credit_enabled'            => true,
                'credit_duration_months'    => 6,
                'credit_installments_count' => 6,
                'created_at'               => now(),
                'updated_at'               => now(),
            ]
        );
        $productLaptopId = DB::table('products')->where('sku', 'LAPTOP-PRO-14')->value('id');

        DB::table('products')->updateOrInsert(
            ['sku' => 'ROUTER-4G'],
            [
                'type'                      => 'simple',
                'name'                      => 'Routeur 4G',
                'description'               => 'Routeur 4G avec batterie.',
                'price'                     => 650000,
                'is_published'              => true,
                'is_active'                 => true,
                'credit_enabled'            => false,
                'credit_duration_months'    => null,
                'credit_installments_count' => null,
                'created_at'               => now(),
                'updated_at'               => now(),
            ]
        );

        // ── Variable product + variants ───────────────────────────────
        DB::table('products')->updateOrInsert(
            ['name' => 'T-Shirt CAURISHOP', 'type' => 'variable'],
            [
                'description'               => 'T-shirt avec variantes de taille et couleur.',
                'sku'                       => null,
                'price'                     => null,
                'is_published'              => true,
                'is_active'                 => true,
                'credit_enabled'            => true,
                'credit_duration_months'    => 3,
                'credit_installments_count' => 3,
                'created_at'               => now(),
                'updated_at'               => now(),
            ]
        );
        $productShirtId = DB::table('products')
            ->where('name', 'T-Shirt CAURISHOP')
            ->where('type', 'variable')
            ->value('id');

        $variants = [
            ['sku' => 'TSHIRT-BLK-M', 'name' => 'Noir - M',   'attrs' => ['color' => 'black', 'size' => 'M'], 'price' => 120000],
            ['sku' => 'TSHIRT-BLK-L', 'name' => 'Noir - L',   'attrs' => ['color' => 'black', 'size' => 'L'], 'price' => 120000],
            ['sku' => 'TSHIRT-WHT-M', 'name' => 'Blanc - M',  'attrs' => ['color' => 'white', 'size' => 'M'], 'price' => 110000],
        ];

        foreach ($variants as $v) {
            DB::table('product_variants')->updateOrInsert(
                ['sku' => $v['sku']],
                [
                    'product_id'                => $productShirtId,
                    'name'                      => $v['name'],
                    'attributes'                => json_encode($v['attrs']),
                    'price'                     => $v['price'],
                    'is_active'                 => true,
                    'credit_enabled'            => null,
                    'credit_duration_months'    => null,
                    'credit_installments_count' => null,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]
            );
        }
    }
}
