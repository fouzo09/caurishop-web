<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ----- Companies
        $companyAId = DB::table('companies')->insertGetId([
            'name' => 'CAURISHOP Entreprise A',
            'registration_number' => 'RCCM-0001',
            'email' => 'contactA@company.test',
            'phone' => '+224 000 000 001',
            'address' => 'Conakry',
            'city' => 'Conakry',
            'country' => 'GN',
            'credit_limit' => 5000000,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $companyBId = DB::table('companies')->insertGetId([
            'name' => 'CAURISHOP Entreprise B',
            'registration_number' => 'RCCM-0002',
            'email' => 'contactB@company.test',
            'phone' => '+224 000 000 002',
            'address' => 'Conakry',
            'city' => 'Conakry',
            'country' => 'GN',
            'credit_limit' => 10000000,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ----- Customers
        $customerIndividualId = DB::table('customers')->insertGetId([
            'type' => 'individual',
            'company_id' => null,
            'first_name' => 'Mamadou',
            'last_name' => 'Diallo',
            'company_contact_name' => null,
            'email' => 'mamadou@client.test',
            'phone' => '+224 600 000 000',
            'address' => 'Conakry',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customerCompanyId = DB::table('customers')->insertGetId([
            'type' => 'company',
            'company_id' => $companyAId,
            'first_name' => null,
            'last_name' => null,
            'company_contact_name' => 'Responsable Achats',
            'email' => 'achats@companyA.test',
            'phone' => '+224 610 000 000',
            'address' => 'Conakry',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ----- Products (simple)
        $productLaptopId = DB::table('products')->insertGetId([
            'type' => 'simple',
            'name' => 'Laptop Pro 14',
            'description' => 'Ordinateur portable pour professionnels.',
            'sku' => 'LAPTOP-PRO-14',
            'price' => 12000000,
            'is_published' => true,
            'is_active' => true,
            'credit_enabled' => true,
            'credit_duration_months' => 6,
            'credit_installments_count' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productRouterId = DB::table('products')->insertGetId([
            'type' => 'simple',
            'name' => 'Routeur 4G',
            'description' => 'Routeur 4G avec batterie.',
            'sku' => 'ROUTER-4G',
            'price' => 650000,
            'is_published' => true,
            'is_active' => true,
            'credit_enabled' => false,
            'credit_duration_months' => null,
            'credit_installments_count' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ----- Product (variable) + variants
        $productShirtId = DB::table('products')->insertGetId([
            'type' => 'variable',
            'name' => 'T-Shirt CAURISHOP',
            'description' => 'T-shirt avec variantes de taille et couleur.',
            'sku' => null,
            'price' => null,
            'is_published' => true,
            'is_active' => true,
            'credit_enabled' => true,
            'credit_duration_months' => 3,
            'credit_installments_count' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $variants = [
            ['sku' => 'TSHIRT-BLK-M', 'name' => 'Noir - M', 'attrs' => ['color' => 'black', 'size' => 'M'], 'price' => 120000],
            ['sku' => 'TSHIRT-BLK-L', 'name' => 'Noir - L', 'attrs' => ['color' => 'black', 'size' => 'L'], 'price' => 120000],
            ['sku' => 'TSHIRT-WHT-M', 'name' => 'Blanc - M', 'attrs' => ['color' => 'white', 'size' => 'M'], 'price' => 110000],
        ];

        foreach ($variants as $v) {
            DB::table('product_variants')->insert([
                'product_id' => $productShirtId,
                'sku' => $v['sku'],
                'name' => $v['name'],
                'attributes' => json_encode($v['attrs']),
                'price' => $v['price'],
                'is_active' => true,

                // override null = hérite du produit (si tu appliques cette règle côté code)
                'credit_enabled' => null,
                'credit_duration_months' => null,
                'credit_installments_count' => null,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Note: On ne crée pas de commandes ici (optionnel),
        // car souvent on veut tester l’UI/workflow à la main.
    }
}
