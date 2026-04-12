<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServicesSeeder extends Seeder
{
    private array $services = [

        // ══════════ BEAUTÉ ══════════
        [
            'category' => 'Beauté',
            'provider' => 'Beauty Palace',
            'type'     => 'simple',
            'name'     => 'Soin du visage hydratant',
            'desc'     => 'Soin du visage en profondeur avec nettoyage, gommage, masque hydratant et sérum vitamine C. Durée : 60 min.',
            'price'    => 180000,
            'credit'   => false,
            'images'   => ['photo-1570172619644-dfd03ed5d881', 'photo-1519415510236-718bdfcd89c8'],
        ],
        [
            'category' => 'Beauté',
            'provider' => 'Beauty Palace',
            'type'     => 'variable',
            'name'     => 'Manucure & Pédicure',
            'desc'     => 'Soin complet des mains et pieds avec pose de vernis semi-permanent. Choix de la prestation selon vos besoins.',
            'price'    => null,
            'credit'   => false,
            'variants' => [
                ['name' => 'Manucure simple',     'sku' => 'SVC-MANI-S',  'price' => 80000,  'attrs' => ['prestation' => 'Manucure']],
                ['name' => 'Pédicure simple',     'sku' => 'SVC-PEDI-S',  'price' => 90000,  'attrs' => ['prestation' => 'Pédicure']],
                ['name' => 'Manucure + Pédicure', 'sku' => 'SVC-MANI-P',  'price' => 150000, 'attrs' => ['prestation' => 'Manucure + Pédicure']],
            ],
            'images'   => ['photo-1604654894610-df63bc536371', 'photo-1604654894610-df63bc536371'],
        ],
        [
            'category' => 'Beauté',
            'provider' => 'Glam Studio',
            'type'     => 'variable',
            'name'     => 'Épilation corporelle',
            'desc'     => 'Épilation à la cire chaude ou froide, réalisée par des esthéticiennes professionnelles. Zones au choix.',
            'price'    => null,
            'credit'   => false,
            'variants' => [
                ['name' => 'Demi-jambes',    'sku' => 'SVC-EPIL-DJ',  'price' => 70000,  'attrs' => ['zone' => 'Demi-jambes']],
                ['name' => 'Jambes entières','sku' => 'SVC-EPIL-JE',  'price' => 110000, 'attrs' => ['zone' => 'Jambes entières']],
                ['name' => 'Aisselles',      'sku' => 'SVC-EPIL-AX',  'price' => 45000,  'attrs' => ['zone' => 'Aisselles']],
                ['name' => 'Corps complet',  'sku' => 'SVC-EPIL-CC',  'price' => 220000, 'attrs' => ['zone' => 'Corps complet']],
            ],
            'images'   => ['photo-1560869713-7d0a29430803', 'photo-1522337360788-8b13dee7a37e'],
        ],
        [
            'category' => 'Beauté',
            'provider' => 'Glam Studio',
            'type'     => 'simple',
            'name'     => 'Coiffure & Brushing',
            'desc'     => 'Coupe, soin kératine, brushing ou tressage afro réalisé par nos coiffeurs experts. Résultat naturel et longue durée.',
            'price'    => 120000,
            'credit'   => false,
            'images'   => ['photo-1522337360788-8b13dee7a37e', 'photo-1562322140-8baeececf3df'],
        ],
        [
            'category' => 'Beauté',
            'provider' => 'Beauty Palace',
            'type'     => 'variable',
            'name'     => 'Maquillage professionnel',
            'desc'     => 'Maquillage réalisé par une make-up artist certifiée. Produits premium longue tenue, idéal pour événements.',
            'price'    => null,
            'credit'   => false,
            'variants' => [
                ['name' => 'Maquillage jour',   'sku' => 'SVC-MAKE-D',  'price' => 100000, 'attrs' => ['occasion' => 'Jour']],
                ['name' => 'Maquillage soirée', 'sku' => 'SVC-MAKE-S',  'price' => 160000, 'attrs' => ['occasion' => 'Soirée']],
                ['name' => 'Maquillage mariée', 'sku' => 'SVC-MAKE-M',  'price' => 350000, 'attrs' => ['occasion' => 'Mariage']],
            ],
            'images'   => ['photo-1487412947147-5cebf100ffc2', 'photo-1516975080664-ed2fc6a32937'],
        ],

        // ══════════ SPORT ══════════
        [
            'category' => 'Sport',
            'provider' => 'Coach Pro Conakry',
            'type'     => 'variable',
            'name'     => 'Coaching sportif personnel',
            'desc'     => 'Séances de coaching individuel avec un coach certifié. Programme sur-mesure : perte de poids, prise de masse, remise en forme.',
            'price'    => null,
            'credit'   => true,
            'installments' => 3,
            'duration'     => 3,
            'variants' => [
                ['name' => '1 séance',    'sku' => 'SVC-COACH-1',   'price' => 150000,  'attrs' => ['formule' => '1 séance']],
                ['name' => '5 séances',   'sku' => 'SVC-COACH-5',   'price' => 650000,  'attrs' => ['formule' => '5 séances']],
                ['name' => '10 séances',  'sku' => 'SVC-COACH-10',  'price' => 1200000, 'attrs' => ['formule' => '10 séances']],
                ['name' => '1 mois',      'sku' => 'SVC-COACH-1M',  'price' => 1800000, 'attrs' => ['formule' => '1 mois illimité']],
            ],
            'images'   => ['photo-1571019614242-c5c5dee9f50b', 'photo-1517836357463-d25dfeac3438'],
        ],
        [
            'category' => 'Sport',
            'provider' => 'AquaFit Center',
            'type'     => 'variable',
            'name'     => 'Cours de natation',
            'desc'     => 'Cours de natation encadrés par des maîtres nageurs diplômés. Tous niveaux, de débutant à confirmé.',
            'price'    => null,
            'credit'   => false,
            'variants' => [
                ['name' => 'Débutant – 1 séance',   'sku' => 'SVC-SWIM-D1', 'price' => 80000,  'attrs' => ['niveau' => 'Débutant',  'formule' => '1 séance']],
                ['name' => 'Confirmé – 1 séance',   'sku' => 'SVC-SWIM-C1', 'price' => 90000,  'attrs' => ['niveau' => 'Confirmé',  'formule' => '1 séance']],
                ['name' => 'Débutant – 8 séances',  'sku' => 'SVC-SWIM-D8', 'price' => 550000, 'attrs' => ['niveau' => 'Débutant',  'formule' => '8 séances']],
                ['name' => 'Confirmé – 8 séances',  'sku' => 'SVC-SWIM-C8', 'price' => 620000, 'attrs' => ['niveau' => 'Confirmé',  'formule' => '8 séances']],
            ],
            'images'   => ['photo-1530549387789-4c1017266635', 'photo-1560090995-01632a28895b'],
        ],
        [
            'category' => 'Sport',
            'provider' => 'Zen Yoga Studio',
            'type'     => 'variable',
            'name'     => 'Cours de yoga & méditation',
            'desc'     => 'Cours de yoga Hatha, Vinyasa ou méditation pleine conscience. Accessible à tous, tapis fourni.',
            'price'    => null,
            'credit'   => false,
            'variants' => [
                ['name' => '1 cours',          'sku' => 'SVC-YOGA-1',  'price' => 70000,  'attrs' => ['formule' => '1 cours']],
                ['name' => '5 cours',          'sku' => 'SVC-YOGA-5',  'price' => 300000, 'attrs' => ['formule' => '5 cours']],
                ['name' => 'Abonnement mensuel','sku' => 'SVC-YOGA-M',  'price' => 500000, 'attrs' => ['formule' => 'Mensuel illimité']],
            ],
            'images'   => ['photo-1544367567-0f2fcb009e0b', 'photo-1506126613408-eca07ce68773'],
        ],
        [
            'category' => 'Sport',
            'provider' => 'Combat Academy',
            'type'     => 'variable',
            'name'     => 'Arts martiaux & self-défense',
            'desc'     => 'Cours de Taekwondo, Karaté ou Judo dispensés par des ceintures noires. Enfants et adultes bienvenus.',
            'price'    => null,
            'credit'   => false,
            'variants' => [
                ['name' => 'Enfant – 1 mois',  'sku' => 'SVC-MART-E1', 'price' => 250000, 'attrs' => ['tranche' => 'Enfant',  'durée' => '1 mois']],
                ['name' => 'Adulte – 1 mois',  'sku' => 'SVC-MART-A1', 'price' => 350000, 'attrs' => ['tranche' => 'Adulte',  'durée' => '1 mois']],
                ['name' => 'Adulte – 3 mois',  'sku' => 'SVC-MART-A3', 'price' => 900000, 'attrs' => ['tranche' => 'Adulte',  'durée' => '3 mois']],
            ],
            'images'   => ['photo-1555597673-b21d5c935865', 'photo-1517438476312-10d79c077509'],
        ],
        [
            'category' => 'Sport',
            'provider' => 'Coach Pro Conakry',
            'type'     => 'variable',
            'name'     => 'Cours de pilates',
            'desc'     => 'Pilates mat ou reformer pour renforcer la sangle abdominale, améliorer la posture et la souplesse. Petits groupes de 6 max.',
            'price'    => null,
            'credit'   => false,
            'variants' => [
                ['name' => '1 cours',          'sku' => 'SVC-PILATES-1', 'price' => 90000,  'attrs' => ['formule' => '1 cours']],
                ['name' => '8 cours',          'sku' => 'SVC-PILATES-8', 'price' => 640000, 'attrs' => ['formule' => '8 cours']],
                ['name' => 'Abonnement 1 mois','sku' => 'SVC-PILATES-M', 'price' => 750000, 'attrs' => ['formule' => '1 mois illimité']],
            ],
            'images'   => ['photo-1518611012118-696072aa579a', 'photo-1474418397713-7ede21d49118'],
        ],

        // ══════════ GYM ══════════
        [
            'category' => 'Gym',
            'provider' => 'FitZone Conakry',
            'type'     => 'variable',
            'name'     => 'Abonnement salle de gym',
            'desc'     => 'Accès illimité à l\'espace muscu, cardio, sauna et cours collectifs de FitZone Conakry. Équipements haut de gamme.',
            'price'    => null,
            'credit'   => true,
            'installments' => 3,
            'duration'     => 3,
            'variants' => [
                ['name' => '1 mois',    'sku' => 'GYM-ABO-1M',  'price' => 400000,  'attrs' => ['durée' => '1 mois']],
                ['name' => '3 mois',    'sku' => 'GYM-ABO-3M',  'price' => 1050000, 'attrs' => ['durée' => '3 mois']],
                ['name' => '6 mois',    'sku' => 'GYM-ABO-6M',  'price' => 1800000, 'attrs' => ['durée' => '6 mois']],
                ['name' => '1 an',      'sku' => 'GYM-ABO-1Y',  'price' => 3200000, 'attrs' => ['durée' => '1 an']],
            ],
            'images'   => ['photo-1534438327276-14e5300c3a48', 'photo-1571902943202-507ec2618e8f'],
        ],
        [
            'category' => 'Gym',
            'provider' => 'PowerGym Elite',
            'type'     => 'variable',
            'name'     => 'Programme musculation personnalisé',
            'desc'     => 'Programme de musculation 100 % adapté à votre morphologie et vos objectifs. Suivi hebdomadaire inclus.',
            'price'    => null,
            'credit'   => false,
            'variants' => [
                ['name' => 'Prise de masse – 1 mois',  'sku' => 'GYM-PROG-PM1', 'price' => 600000,  'attrs' => ['objectif' => 'Prise de masse',  'durée' => '1 mois']],
                ['name' => 'Prise de masse – 3 mois',  'sku' => 'GYM-PROG-PM3', 'price' => 1500000, 'attrs' => ['objectif' => 'Prise de masse',  'durée' => '3 mois']],
                ['name' => 'Perte de poids – 1 mois',  'sku' => 'GYM-PROG-PP1', 'price' => 600000,  'attrs' => ['objectif' => 'Perte de poids',  'durée' => '1 mois']],
                ['name' => 'Perte de poids – 3 mois',  'sku' => 'GYM-PROG-PP3', 'price' => 1500000, 'attrs' => ['objectif' => 'Perte de poids',  'durée' => '3 mois']],
            ],
            'images'   => ['photo-1583454110551-21f2fa2afe61', 'photo-1526506118085-60ce8714f8c5'],
        ],
        [
            'category' => 'Gym',
            'provider' => 'FitZone Conakry',
            'type'     => 'variable',
            'name'     => 'Cours collectifs fitness',
            'desc'     => 'Zumba, Step, Body Combat, Cycling ou HIIT. Cours en groupe animés par des coachs dynamiques et certifiés.',
            'price'    => null,
            'credit'   => false,
            'variants' => [
                ['name' => 'Cours à l\'unité',  'sku' => 'GYM-COURS-1', 'price' => 65000,  'attrs' => ['formule' => '1 cours']],
                ['name' => '10 cours',          'sku' => 'GYM-COURS-10','price' => 550000, 'attrs' => ['formule' => '10 cours']],
                ['name' => 'Mensuel illimité',  'sku' => 'GYM-COURS-M', 'price' => 480000, 'attrs' => ['formule' => 'Mensuel illimité']],
            ],
            'images'   => ['photo-1518611012118-696072aa579a', 'photo-1571019613454-1cb2f99b2d8b'],
        ],

        // ══════════ MASSAGE ══════════
        [
            'category' => 'Massage',
            'provider' => 'Studio Zen',
            'type'     => 'variable',
            'name'     => 'Massage relaxant',
            'desc'     => 'Massage suédois aux huiles essentielles pour relâcher les tensions musculaires et favoriser un profond état de détente.',
            'price'    => null,
            'credit'   => false,
            'variants' => [
                ['name' => '30 minutes', 'sku' => 'SVC-MASS-R30', 'price' => 120000, 'attrs' => ['durée' => '30 min']],
                ['name' => '60 minutes', 'sku' => 'SVC-MASS-R60', 'price' => 210000, 'attrs' => ['durée' => '60 min']],
                ['name' => '90 minutes', 'sku' => 'SVC-MASS-R90', 'price' => 290000, 'attrs' => ['durée' => '90 min']],
            ],
            'images'   => ['photo-1544161515-4ab6ce6db874', 'photo-1600334089648-b0d9d3028eb2'],
        ],
        [
            'category' => 'Massage',
            'provider' => 'Studio Zen',
            'type'     => 'variable',
            'name'     => 'Massage sportif',
            'desc'     => 'Massage deep-tissue ciblant les groupes musculaires sollicités par le sport. Idéal avant ou après l\'effort.',
            'price'    => null,
            'credit'   => false,
            'variants' => [
                ['name' => '45 minutes', 'sku' => 'SVC-MASS-S45', 'price' => 180000, 'attrs' => ['durée' => '45 min']],
                ['name' => '60 minutes', 'sku' => 'SVC-MASS-S60', 'price' => 240000, 'attrs' => ['durée' => '60 min']],
            ],
            'images'   => ['photo-1571019614242-c5c5dee9f50b', 'photo-1517836357463-d25dfeac3438'],
        ],
        [
            'category' => 'Massage',
            'provider' => 'Wellness Center',
            'type'     => 'variable',
            'name'     => 'Massage aux pierres chaudes',
            'desc'     => 'Massage thérapeutique utilisant des pierres volcaniques chauffées pour détendre les muscles en profondeur et rééquilibrer l\'énergie.',
            'price'    => null,
            'credit'   => false,
            'variants' => [
                ['name' => '60 minutes', 'sku' => 'SVC-MASS-P60', 'price' => 280000, 'attrs' => ['durée' => '60 min']],
                ['name' => '90 minutes', 'sku' => 'SVC-MASS-P90', 'price' => 380000, 'attrs' => ['durée' => '90 min']],
            ],
            'images'   => ['photo-1600334089648-b0d9d3028eb2', 'photo-1544161515-4ab6ce6db874'],
        ],
        [
            'category' => 'Massage',
            'provider' => 'Wellness Center',
            'type'     => 'variable',
            'name'     => 'Massage thaïlandais',
            'desc'     => 'Technique ancestrale alliant acupression et étirements passifs. Améliore la circulation, la souplesse et le bien-être global.',
            'price'    => null,
            'credit'   => false,
            'variants' => [
                ['name' => '60 minutes',  'sku' => 'SVC-MASS-T60', 'price' => 250000, 'attrs' => ['durée' => '60 min']],
                ['name' => '90 minutes',  'sku' => 'SVC-MASS-T90', 'price' => 350000, 'attrs' => ['durée' => '90 min']],
                ['name' => '120 minutes', 'sku' => 'SVC-MASS-T120','price' => 440000, 'attrs' => ['durée' => '120 min']],
            ],
            'images'   => ['photo-1519823551278-64ac92734fb1', 'photo-1515377905703-c4788e51af15'],
        ],
        [
            'category' => 'Massage',
            'provider' => 'Studio Zen',
            'type'     => 'simple',
            'name'     => 'Forfait Spa journée complète',
            'desc'     => 'Journée bien-être incluant : accueil tisane, bain à remous, hammam, massage 60 min et soin du visage. Pour 1 personne.',
            'price'    => 850000,
            'credit'   => true,
            'installments' => 2,
            'duration'     => 2,
            'images'   => ['photo-1540555700478-4be289fbecef', 'photo-1519823551278-64ac92734fb1'],
        ],
    ];

    public function run(): void
    {
        foreach ($this->services as $data) {
            if (Product::where('name', $data['name'])->exists()) {
                $this->command->line("  ⏭  {$data['name']} — existe déjà.");
                continue;
            }

            $this->command->line("\n  [{$data['category']}] <fg=cyan>{$data['name']}</> — {$data['provider']}");

            $product = Product::create([
                'type'                      => $data['type'],
                'name'                      => $data['name'],
                'slug'                      => Str::slug($data['name']),
                'description'               => $data['desc'],
                'sku'                       => $data['type'] === 'simple' ? Str::upper(Str::slug($data['name'], '-')) : null,
                'price'                     => $data['price'] ?? null,
                'stock_quantity'            => 0,
                'low_stock_threshold'       => 0,
                'is_published'              => true,
                'is_active'                 => true,
                'is_service'                => true,
                'provider'                  => $data['provider'],
                'credit_enabled'            => $data['credit'] ?? false,
                'credit_duration_months'    => $data['duration'] ?? null,
                'credit_installments_count' => $data['installments'] ?? null,
            ]);

            // Variantes
            if ($data['type'] === 'variable' && !empty($data['variants'])) {
                foreach ($data['variants'] as $v) {
                    ProductVariant::create([
                        'product_id'               => $product->id,
                        'sku'                      => $v['sku'],
                        'name'                     => $v['name'],
                        'attributes'               => $v['attrs'],
                        'price'                    => $v['price'],
                        'stock_quantity'           => 0,
                        'is_active'                => true,
                        'credit_enabled'           => null,
                        'credit_duration_months'   => null,
                        'credit_installments_count'=> null,
                    ]);
                }
                $this->command->line("     → " . count($data['variants']) . " variantes");
            }

            // Images
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

        $total = Product::where('is_service', true)->count();
        $this->command->newLine();
        $this->command->info("  ✅  {$total} services en catalogue.");
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
