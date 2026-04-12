<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    // ─────────────────────────────────────────────────────────────
    // Catalogue
    // type: simple | variable
    // variants: [name, sku_suffix, price, stock, attributes]
    // images: Unsplash photo IDs
    // ─────────────────────────────────────────────────────────────
    private array $catalog = [

        // ══════════ TECH ══════════
        [
            'category'     => 'Tech',
            'type'         => 'simple',
            'name'         => 'Samsung Galaxy S25',
            'sku'          => 'SAM-S25',
            'description'  => 'Smartphone Samsung Galaxy S25 avec écran AMOLED 6,2 pouces, processeur Exynos 2500 et triple capteur photo 200 MP.',
            'price'        => 7200000,
            'stock'        => 25,
            'credit'       => true,
            'installments' => 6,
            'duration'     => 6,
            'images'       => [
                'photo-1610945415295-d9bbf067e59c', // Samsung phone front
                'photo-1574944985070-8f3ebc6b79d2', // phone back
            ],
        ],
        [
            'category'     => 'Tech',
            'type'         => 'simple',
            'name'         => 'AirPods Pro 2',
            'sku'          => 'APP-PRO2',
            'description'  => 'Écouteurs sans fil Apple AirPods Pro 2e génération avec réduction de bruit active et son spatial personnalisé.',
            'price'        => 2100000,
            'stock'        => 40,
            'credit'       => false,
            'images'       => [
                'photo-1603351154351-5e2d0600bb77', // AirPods white
                'photo-1589491106932-b4d6aff53b97', // earbuds case
            ],
        ],
        [
            'category'     => 'Tech',
            'type'         => 'variable',
            'name'         => 'iPad Pro 12.9"',
            'sku'          => null,
            'description'  => 'Tablette Apple iPad Pro 12,9 pouces avec puce M4, écran Liquid Retina XDR et compatibilité Apple Pencil Pro.',
            'price'        => null,
            'credit'       => true,
            'installments' => 12,
            'duration'     => 12,
            'variants'     => [
                ['name' => 'Gris Sidéral 256 Go', 'sku' => 'IPAD-PRO-GS-256', 'price' => 9500000, 'stock' => 10, 'attrs' => ['couleur' => 'Gris Sidéral', 'stockage' => '256 Go']],
                ['name' => 'Argent 256 Go',        'sku' => 'IPAD-PRO-AR-256', 'price' => 9500000, 'stock' => 10, 'attrs' => ['couleur' => 'Argent',        'stockage' => '256 Go']],
                ['name' => 'Gris Sidéral 512 Go', 'sku' => 'IPAD-PRO-GS-512', 'price' => 11500000,'stock' => 8,  'attrs' => ['couleur' => 'Gris Sidéral', 'stockage' => '512 Go']],
                ['name' => 'Argent 1 To',          'sku' => 'IPAD-PRO-AR-1T',  'price' => 14500000,'stock' => 5,  'attrs' => ['couleur' => 'Argent',        'stockage' => '1 To']],
            ],
            'images'       => [
                'photo-1544244015-0df4b3ffc6b0', // iPad sur bureau
                'photo-1559526324-593bc073d938', // iPad Pro vue arrière
                'photo-1627384113743-6bd5a479fffd', // iPad stylus
            ],
        ],
        [
            'category'     => 'Tech',
            'type'         => 'variable',
            'name'         => 'Clavier Mécanique RGB',
            'sku'          => null,
            'description'  => 'Clavier mécanique gaming avec rétroéclairage RGB personnalisable, switches Cherry MX et repose-poignets magnétique.',
            'price'        => null,
            'credit'       => false,
            'variants'     => [
                ['name' => 'Noir – Switches Rouges', 'sku' => 'KB-RGB-BK-R', 'price' => 890000, 'stock' => 20, 'attrs' => ['couleur' => 'Noir',  'switches' => 'Rouge']],
                ['name' => 'Blanc – Switches Rouges','sku' => 'KB-RGB-WH-R', 'price' => 890000, 'stock' => 15, 'attrs' => ['couleur' => 'Blanc', 'switches' => 'Rouge']],
                ['name' => 'Noir – Switches Bleus',  'sku' => 'KB-RGB-BK-B', 'price' => 950000, 'stock' => 12, 'attrs' => ['couleur' => 'Noir',  'switches' => 'Bleu']],
            ],
            'images'       => [
                'photo-1587829741301-dc798b83add3', // keyboard RGB
                'photo-1541140532154-b024d705b90a', // mechanical keys close-up
            ],
        ],
        [
            'category'     => 'Tech',
            'type'         => 'variable',
            'name'         => 'Disque SSD Externe',
            'sku'          => null,
            'description'  => 'Disque SSD portable ultra-rapide, résistant aux chocs et à l\'eau, avec interface USB-C 3.2 Gen 2 (1 000 Mo/s).',
            'price'        => null,
            'credit'       => false,
            'variants'     => [
                ['name' => '500 Go', 'sku' => 'SSD-EXT-500', 'price' => 680000,  'stock' => 30, 'attrs' => ['capacité' => '500 Go']],
                ['name' => '1 To',   'sku' => 'SSD-EXT-1T',  'price' => 1100000, 'stock' => 25, 'attrs' => ['capacité' => '1 To']],
                ['name' => '2 To',   'sku' => 'SSD-EXT-2T',  'price' => 1900000, 'stock' => 15, 'attrs' => ['capacité' => '2 To']],
            ],
            'images'       => [
                'photo-1531492746076-161ca9bcad58', // portable SSD
                'photo-1558494949-ef010cbdcc31', // USB drive
            ],
        ],

        // ══════════ MODE & STYLE ══════════
        [
            'category'     => 'Mode',
            'type'         => 'variable',
            'name'         => 'Sneakers Nike Air Max',
            'sku'          => null,
            'description'  => 'Chaussures de sport Nike Air Max avec amorti Air visible et semelle en caoutchouc durable. Confort au quotidien.',
            'price'        => null,
            'credit'       => false,
            'variants'     => [
                ['name' => 'Blanc – T40', 'sku' => 'NIKE-AM-WH-40', 'price' => 1300000, 'stock' => 8,  'attrs' => ['couleur' => 'Blanc', 'pointure' => '40']],
                ['name' => 'Blanc – T41', 'sku' => 'NIKE-AM-WH-41', 'price' => 1300000, 'stock' => 10, 'attrs' => ['couleur' => 'Blanc', 'pointure' => '41']],
                ['name' => 'Blanc – T42', 'sku' => 'NIKE-AM-WH-42', 'price' => 1300000, 'stock' => 10, 'attrs' => ['couleur' => 'Blanc', 'pointure' => '42']],
                ['name' => 'Noir – T41',  'sku' => 'NIKE-AM-BK-41', 'price' => 1350000, 'stock' => 6,  'attrs' => ['couleur' => 'Noir',  'pointure' => '41']],
                ['name' => 'Noir – T42',  'sku' => 'NIKE-AM-BK-42', 'price' => 1350000, 'stock' => 7,  'attrs' => ['couleur' => 'Noir',  'pointure' => '42']],
            ],
            'images'       => [
                'photo-1542291026-7eec264c27ff', // Nike sneaker side
                'photo-1595950653106-6c9ebd614d3a', // sneakers pair
                'photo-1600185365926-3a2ce3cdb9eb', // sneaker detail
            ],
        ],
        [
            'category'     => 'Mode',
            'type'         => 'simple',
            'name'         => 'Montre Classique Or',
            'sku'          => 'WATCH-GOLD',
            'description'  => 'Montre analogique élégante avec boîtier en acier inoxydable doré, bracelet en cuir véritable et verre saphir anti-rayures.',
            'price'        => 1750000,
            'stock'        => 15,
            'credit'       => true,
            'installments' => 3,
            'duration'     => 3,
            'images'       => [
                'photo-1523275335684-37898b6baf30', // watch gold
                'photo-1508685096489-7aacd43bd3b1', // luxury watch detail
            ],
        ],
        [
            'category'     => 'Mode',
            'type'         => 'variable',
            'name'         => 'Sac à Main Cuir',
            'sku'          => null,
            'description'  => 'Sac à main en cuir véritable de qualité supérieure, coutures renforcées, fermeture dorée et compartiments multiples.',
            'price'        => null,
            'credit'       => false,
            'variants'     => [
                ['name' => 'Noir',   'sku' => 'BAG-LEATHER-BK', 'price' => 1200000, 'stock' => 12, 'attrs' => ['couleur' => 'Noir']],
                ['name' => 'Marron', 'sku' => 'BAG-LEATHER-BR', 'price' => 1200000, 'stock' => 10, 'attrs' => ['couleur' => 'Marron']],
                ['name' => 'Beige',  'sku' => 'BAG-LEATHER-BE', 'price' => 1250000, 'stock' => 8,  'attrs' => ['couleur' => 'Beige']],
            ],
            'images'       => [
                'photo-1548036328-c9fa89d128fa', // leather handbag
                'photo-1584917865442-de89df76afd3', // bag detail
            ],
        ],
        [
            'category'     => 'Mode',
            'type'         => 'simple',
            'name'         => 'Lunettes de Soleil Aviateur',
            'sku'          => 'SUNGLASS-AVI',
            'description'  => 'Lunettes de soleil style aviateur avec monture en métal doré, verres polarisés UV400 et étui de protection inclus.',
            'price'        => 430000,
            'stock'        => 35,
            'credit'       => false,
            'images'       => [
                'photo-1511499767150-a48a237f0083', // sunglasses
                'photo-1577803645773-f96470509666', // aviator glasses
            ],
        ],
        [
            'category'     => 'Mode',
            'type'         => 'variable',
            'name'         => 'Parfum Prestige',
            'sku'          => null,
            'description'  => 'Eau de parfum de luxe aux notes de bois de santal, rose de Damas et musc blanc. Longue tenue 8 à 10 heures.',
            'price'        => null,
            'credit'       => false,
            'variants'     => [
                ['name' => '50 ml',  'sku' => 'PERF-PRES-50',  'price' => 520000,  'stock' => 20, 'attrs' => ['contenance' => '50 ml']],
                ['name' => '100 ml', 'sku' => 'PERF-PRES-100', 'price' => 870000,  'stock' => 15, 'attrs' => ['contenance' => '100 ml']],
                ['name' => '200 ml', 'sku' => 'PERF-PRES-200', 'price' => 1400000, 'stock' => 8,  'attrs' => ['contenance' => '200 ml']],
            ],
            'images'       => [
                'photo-1541643600914-78b084683702', // perfume bottle
                'photo-1588776814546-1ffcf47267a5', // perfume luxury
            ],
        ],

        // ══════════ ALIMENTAIRE ══════════
        [
            'category'     => 'Alimentaire',
            'type'         => 'variable',
            'name'         => 'Café Arabica Premium',
            'sku'          => null,
            'description'  => 'Café 100 % Arabica de haute altitude, torréfaction artisanale, notes de caramel et de fruits rouges. Origine Éthiopie.',
            'price'        => null,
            'credit'       => false,
            'variants'     => [
                ['name' => '250 g – Grains', 'sku' => 'CAFE-ARB-250G', 'price' => 180000, 'stock' => 50, 'attrs' => ['poids' => '250 g', 'forme' => 'Grains']],
                ['name' => '500 g – Grains', 'sku' => 'CAFE-ARB-500G', 'price' => 320000, 'stock' => 40, 'attrs' => ['poids' => '500 g', 'forme' => 'Grains']],
                ['name' => '250 g – Moulu',  'sku' => 'CAFE-ARB-250M', 'price' => 185000, 'stock' => 40, 'attrs' => ['poids' => '250 g', 'forme' => 'Moulu']],
                ['name' => '1 kg – Grains',  'sku' => 'CAFE-ARB-1KG',  'price' => 580000, 'stock' => 25, 'attrs' => ['poids' => '1 kg',  'forme' => 'Grains']],
            ],
            'images'       => [
                'photo-1559056199-641a0ac8b55e', // coffee beans
                'photo-1495474472287-4d71bcdd2085', // coffee cup
                'photo-1514432324607-a09d9b4aefdd', // coffee bag
            ],
        ],
        [
            'category'     => 'Alimentaire',
            'type'         => 'simple',
            'name'         => 'Huile d\'Olive Extra Vierge',
            'sku'          => 'HUILE-OL-EV',
            'description'  => 'Huile d\'olive extra vierge première pression à froid, acidité < 0,3 %, bouteille en verre foncé 750 ml. Origine Tunisie.',
            'price'        => 215000,
            'stock'        => 60,
            'credit'       => false,
            'images'       => [
                'photo-1474979266404-7eaacbcd87c5', // olive oil bottle
                'photo-1558449028-b53a39d100fc', // olive oil pouring
            ],
        ],
        [
            'category'     => 'Alimentaire',
            'type'         => 'variable',
            'name'         => 'Miel Naturel Pur',
            'sku'          => null,
            'description'  => 'Miel 100 % naturel non pasteurisé, récolté à la main par des apiculteurs locaux. Riche en antioxydants et enzymes.',
            'price'        => null,
            'credit'       => false,
            'variants'     => [
                ['name' => 'Miel de Fleurs 250 g',  'sku' => 'MIEL-FL-250', 'price' => 130000, 'stock' => 45, 'attrs' => ['variété' => 'Fleurs',  'poids' => '250 g']],
                ['name' => 'Miel de Fleurs 500 g',  'sku' => 'MIEL-FL-500', 'price' => 235000, 'stock' => 30, 'attrs' => ['variété' => 'Fleurs',  'poids' => '500 g']],
                ['name' => 'Miel d\'Acacia 250 g',  'sku' => 'MIEL-AC-250', 'price' => 155000, 'stock' => 35, 'attrs' => ['variété' => 'Acacia',  'poids' => '250 g']],
                ['name' => 'Miel d\'Acacia 500 g',  'sku' => 'MIEL-AC-500', 'price' => 285000, 'stock' => 25, 'attrs' => ['variété' => 'Acacia',  'poids' => '500 g']],
            ],
            'images'       => [
                'photo-1587049352851-8d4e89133924', // honey jar
                'photo-1558642084-fd07fae5282e',    // honey drip
            ],
        ],
        [
            'category'     => 'Alimentaire',
            'type'         => 'simple',
            'name'         => 'Thé Vert Bio Sencha',
            'sku'          => 'THE-VERT-BIO',
            'description'  => 'Thé vert japonais Sencha biologique, riche en catéchines, goût végétal délicat. Boîte de 40 sachets.',
            'price'        => 135000,
            'stock'        => 80,
            'credit'       => false,
            'images'       => [
                'photo-1556679343-c7306c1976bc', // green tea cup
                'photo-1594631252845-29fc4cc8cde9', // tea leaves
            ],
        ],
        [
            'category'     => 'Alimentaire',
            'type'         => 'variable',
            'name'         => 'Chocolat Artisanal',
            'sku'          => null,
            'description'  => 'Tablettes de chocolat artisanal, préparées avec des fèves de cacao de première qualité. Sans huile de palme.',
            'price'        => null,
            'credit'       => false,
            'variants'     => [
                ['name' => 'Noir 75 % – 100 g',  'sku' => 'CHOC-NOIR-100',  'price' => 88000,  'stock' => 60, 'attrs' => ['type' => 'Noir 75 %',   'poids' => '100 g']],
                ['name' => 'Lait – 100 g',        'sku' => 'CHOC-LAIT-100',  'price' => 82000,  'stock' => 60, 'attrs' => ['type' => 'Lait',         'poids' => '100 g']],
                ['name' => 'Blanc – 100 g',       'sku' => 'CHOC-BLANC-100', 'price' => 80000,  'stock' => 50, 'attrs' => ['type' => 'Blanc',        'poids' => '100 g']],
                ['name' => 'Noir 75 % – 250 g',  'sku' => 'CHOC-NOIR-250',  'price' => 195000, 'stock' => 40, 'attrs' => ['type' => 'Noir 75 %',   'poids' => '250 g']],
                ['name' => 'Lait – 250 g',        'sku' => 'CHOC-LAIT-250',  'price' => 182000, 'stock' => 40, 'attrs' => ['type' => 'Lait',         'poids' => '250 g']],
            ],
            'images'       => [
                'photo-1481391319762-47dff72954d9', // chocolate bar
                'photo-1549007994-cb92caebd54b',    // chocolate pieces
                'photo-1606312619070-d48b5b7a159a', // dark chocolate
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->catalog as $data) {
            // Éviter les doublons
            if (Product::where('name', $data['name'])->exists()) {
                $this->command->line("  ⏭  {$data['name']} — existe déjà, ignoré.");
                continue;
            }

            $this->command->line("\n  [{$data['category']}] <fg=cyan>{$data['name']}</>");

            // ── Créer le produit ────────────────────────────────────
            $product = Product::create([
                'type'                     => $data['type'],
                'name'                     => $data['name'],
                'slug'                     => Str::slug($data['name']),
                'description'              => $data['description'],
                'sku'                      => $data['sku'] ?? null,
                'price'                    => $data['price'] ?? null,
                'stock_quantity'           => $data['stock'] ?? 0,
                'low_stock_threshold'      => 5,
                'is_published'             => true,
                'is_active'                => true,
                'credit_enabled'           => $data['credit'] ?? false,
                'credit_duration_months'   => $data['duration'] ?? null,
                'credit_installments_count'=> $data['installments'] ?? null,
            ]);

            // ── Variantes ───────────────────────────────────────────
            if ($data['type'] === 'variable' && !empty($data['variants'])) {
                foreach ($data['variants'] as $v) {
                    ProductVariant::create([
                        'product_id'               => $product->id,
                        'sku'                      => $v['sku'],
                        'name'                     => $v['name'],
                        'attributes'               => $v['attrs'],
                        'price'                    => $v['price'],
                        'stock_quantity'           => $v['stock'],
                        'is_active'                => true,
                        'credit_enabled'           => null,
                        'credit_duration_months'   => null,
                        'credit_installments_count'=> null,
                    ]);
                }
                $this->command->line("     → " . count($data['variants']) . " variantes créées");
            }

            // ── Images ──────────────────────────────────────────────
            foreach ($data['images'] as $i => $photoId) {
                $url  = "https://images.unsplash.com/{$photoId}?w=800&q=80&fit=crop&auto=format";
                $path = $this->download($url, $product->id, $i);

                if ($path) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path'       => $path,
                        'sort_order' => $i,
                        'is_primary' => $i === 0,
                    ]);
                    $this->command->line("     ✓ Image " . ($i + 1));
                } else {
                    $this->command->warn("     ✗ Échec image " . ($i + 1));
                }
            }
        }

        $this->command->newLine();
        $this->command->info("  ✅  Catalogue importé : " . Product::count() . " produits, " . ProductImage::count() . " images.");
    }

    private function download(string $url, int $productId, int $index): ?string
    {
        try {
            $response = Http::timeout(20)->withOptions(['allow_redirects' => true])->get($url);
            if (!$response->successful()) return null;

            $path = "products/{$productId}/img_{$index}.jpg";
            Storage::disk('public')->put($path, $response->body());
            return $path;
        } catch (\Throwable) {
            return null;
        }
    }
}
