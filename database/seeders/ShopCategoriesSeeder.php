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

        // Index slug => id
        $bySlug = collect($created)->keyBy('slug');

        // Règles d'affectation par mots-clés (ordre = priorité).
        $rules = [
            'informatique'   => ['laptop', 'ordinateur', 'pc ', 'ipad', 'tablette', 'clavier', 'souris', 'ssd', 'disque', 'écran', 'imprimante'],
            'electronique'   => ['iphone', 'samsung', 'galaxy', 'smartphone', 'téléphone', 'airpods', 'écouteur', 'casque', 'routeur', 'télévision', ' tv', 'console', 'scooter', 'gyrocopter', 'electric', 'électrique', 'caméra', 'drone'],
            'mode-vetements' => ['t-shirt', 'tshirt', 'chemise', 'robe', 'pantalon', 'jean', 'veste', 'pull', 'sneakers', 'chaussure', 'basket', 'vêtement', 'pagne'],
            'accessoires'    => ['montre', 'sac', 'lunettes', 'ceinture', 'bijou', 'bracelet', 'foulard', 'portefeuille', 'casquette', 'chapeau'],
            'beaute-sante'   => ['parfum', 'soin', 'manucure', 'pédicure', 'épilation', 'coiffure', 'brushing', 'maquillage', 'massage', 'spa', 'coaching', 'sportif', 'natation', 'yoga', 'méditation', 'arts martiaux', 'pilates', 'gym', 'musculation', 'fitness', 'crème', 'savon', 'karité', 'santé'],
            'maison-cuisine' => ['café', 'huile', 'miel', 'thé', 'chocolat', 'épicerie', 'alimentaire', 'cuisine', 'maison', 'meuble', 'déco', 'ustensile', 'vaisselle', 'coussin', 'chaise'],
        ];

        $default = $bySlug->get('accessoires');

        Product::all()->each(function (Product $product) use ($rules, $bySlug, $default) {
            $name = Str::lower(' ' . $product->name . ' ');
            $target = null;

            foreach ($rules as $slug => $keywords) {
                foreach ($keywords as $kw) {
                    if (str_contains($name, $kw)) {
                        $target = $bySlug->get($slug);
                        break 2;
                    }
                }
            }

            $product->update(['category_id' => ($target ?? $default)->id]);
        });
    }
}
