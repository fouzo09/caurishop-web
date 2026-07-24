<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopCategoriesSeeder extends Seeder
{
    /**
     * Catégories de départ + rattachement des produits existants non catégorisés.
     * Idempotent : n'écrase pas une catégorie déjà présente ni une affectation manuelle.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Électronique',      'icon' => 'fa-mobile-screen'],
            ['name' => 'Mode & Vêtements',  'icon' => 'fa-shirt'],
            ['name' => 'Maison & Cuisine',  'icon' => 'fa-house'],
            ['name' => 'Beauté & Santé',    'icon' => 'fa-spa'],
            ['name' => 'Informatique',      'icon' => 'fa-laptop'],
            ['name' => 'Accessoires',       'icon' => 'fa-bag-shopping'],
        ];

        $created = [];
        foreach ($categories as $i => $data) {
            $created[] = Category::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name'       => $data['name'],
                    'icon'       => $data['icon'],
                    'sort_order' => $i,
                    'is_active'  => true,
                ]
            );
        }

        // Rattache les produits sans catégorie (round-robin) pour rendre la boutique démontrable.
        if (! empty($created)) {
            Product::whereNull('category_id')->get()->each(function (Product $product, int $index) use ($created) {
                $product->update(['category_id' => $created[$index % count($created)]->id]);
            });
        }
    }
}
