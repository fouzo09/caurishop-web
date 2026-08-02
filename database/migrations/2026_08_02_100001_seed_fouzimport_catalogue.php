<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Supplier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Catalogue de démonstration du fournisseur FOUZIMPORT :
 * 10 services et 40 produits (simples et variables), tous avec images.
 *
 * Idempotente : si le fournisseur existe déjà, la migration ne fait rien.
 * Les visuels sont générés par GD, sans dépendance réseau ni fichier externe.
 */
return new class extends Migration {
    private const SUPPLIER = 'FOUZIMPORT';

    /** Palette de fonds pour les visuels générés. */
    private const PALETTE = [
        [31, 79, 214], [26, 138, 74], [176, 123, 10], [192, 57, 43],
        [90, 62, 168], [16, 122, 140], [201, 88, 40], [52, 73, 94],
    ];

    public function up(): void
    {
        if (Supplier::where('name', self::SUPPLIER)->exists()) {
            return;
        }

        $supplier = Supplier::create([
            'name'      => self::SUPPLIER,
            'email'     => 'contact@fouzimport.gn',
            'phone'     => '+224 620 45 45 45',
            'country'   => 'Guinée',
            'website'   => 'https://fouzimport.gn',
            'notes'     => 'Importateur généraliste — catalogue de démonstration.',
            'is_active' => true,
        ]);

        // Catégories existantes, indexées par slug. Le catalogue s'y rattache
        // quand elles sont présentes, sans jamais en créer.
        $categories = Category::query()->pluck('id', 'slug');

        $index = 0;

        foreach ($this->services() as $service) {
            $this->createProduct($supplier->id, $categories, $service + ['is_service' => true], $index++);
        }

        foreach ($this->products() as $product) {
            $this->createProduct($supplier->id, $categories, $product, $index++);
        }
    }

    public function down(): void
    {
        $supplier = Supplier::where('name', self::SUPPLIER)->first();

        if (! $supplier) {
            return;
        }

        foreach (Product::where('supplier_id', $supplier->id)->with('images')->get() as $product) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }

            $product->images()->delete();
            $product->variants()->delete();
            $product->delete();
        }

        $supplier->delete();
    }

    /**
     * @param  \Illuminate\Support\Collection<string, int>  $categories
     * @param  array<string, mixed>  $data
     */
    private function createProduct(int $supplierId, $categories, array $data, int $index): void
    {
        $isService = $data['is_service'] ?? false;
        $variants  = $data['variants'] ?? [];
        $isVariable = ! empty($variants);

        // Un produit variable porte le prix minimum de ses variantes.
        $price = $isVariable ? min(array_column($variants, 'price')) : $data['price'];

        $product = Product::create([
            'type'                => $isVariable ? Product::TYPE_VARIABLE : Product::TYPE_SIMPLE,
            'name'                => $data['name'],
            'slug'                => Str::slug($data['name']) . '-fzi' . $index,
            'category_id'         => $categories[$data['category']] ?? null,
            'description'         => $data['description'],
            'sku'                 => sprintf('FZI-%03d', $index + 1),
            'price'               => $price,
            'supplier_price'      => round($price * 0.72),
            'stock_quantity'      => $isService ? 0 : ($data['stock'] ?? 25),
            'low_stock_threshold' => 5,
            'is_published'        => true,
            'is_active'           => true,
            'is_service'          => $isService,
            'provider'            => $isService ? self::SUPPLIER : null,
            'supplier_id'         => $supplierId,
        ]);

        foreach ($variants as $position => $variant) {
            ProductVariant::create([
                'product_id'     => $product->id,
                'sku'            => $product->sku . '-V' . ($position + 1),
                'name'           => $variant['name'],
                'attributes'     => $variant['attributes'],
                'price'          => $variant['price'],
                'supplier_price' => round($variant['price'] * 0.72),
                'stock_quantity' => $variant['stock'] ?? 12,
                'is_active'      => true,
            ]);
        }

        // Deux visuels par produit : la vignette et une vue secondaire.
        foreach ([0, 1] as $position) {
            // Photo réelle livrée avec le dépôt, sinon visuel généré en repli.
            $path = $this->copyPhoto($product, $position)
                ?? $this->makeImage($product, $data['name'], $index, $position);

            if (! $path) {
                continue;
            }

            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $path,
                'sort_order' => $position,
                'is_primary' => $position === 0,
            ]);
        }
    }

    /**
     * Copie la photo fournie avec le dépôt vers le disque public.
     * Retourne son chemin, ou null si le fichier n'est pas présent.
     */
    private function copyPhoto(Product $product, int $position): ?string
    {
        $source = database_path("seeders/fouzimport-images/{$product->sku}-{$position}.jpg");

        if (! is_file($source)) {
            return null;
        }

        $path = "products/{$product->id}/{$product->sku}-{$position}.jpg";
        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }

    /**
     * Génère un visuel carré et l'enregistre sur le disque public.
     * Retourne son chemin, ou null si GD n'est pas disponible.
     */
    private function makeImage(Product $product, string $label, int $index, int $position): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $size = 900;
        $canvas = imagecreatetruecolor($size, $size);

        [$r, $g, $b] = self::PALETTE[($index + $position) % count(self::PALETTE)];

        // Fond, éclairci sur la seconde vue pour différencier les deux visuels.
        $shade = $position === 0 ? 1.0 : 1.25;
        $background = imagecolorallocate(
            $canvas,
            (int) min(255, $r * $shade),
            (int) min(255, $g * $shade),
            (int) min(255, $b * $shade),
        );
        imagefilledrectangle($canvas, 0, 0, $size, $size, $background);

        // Disques translucides : donne de la matière au fond.
        $veil = imagecolorallocatealpha($canvas, 255, 255, 255, 108);
        imagefilledellipse($canvas, (int) ($size * 0.78), (int) ($size * 0.24), 460, 460, $veil);
        imagefilledellipse($canvas, (int) ($size * 0.18), (int) ($size * 0.86), 620, 620, $veil);

        // Cartouche central clair, sur lequel le texte reste lisible.
        $card = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 90, (int) ($size * 0.36), $size - 90, (int) ($size * 0.64), $card);

        $this->drawLabel($canvas, $label, $size, $r, $g, $b);

        ob_start();
        imagejpeg($canvas, null, 85);
        $binary = ob_get_clean();
        imagedestroy($canvas);

        $path = "products/{$product->id}/fouzimport_{$position}.jpg";
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    /**
     * Écrit le libellé au centre du cartouche. Le texte est rendu sur un petit
     * canevas puis agrandi : GD n'a alors besoin d'aucune police externe.
     */
    private function drawLabel($canvas, string $label, int $size, int $r, int $g, int $b): void
    {
        $lines = $this->wrap($label, 22);
        $font = 5;                       // police interne GD : 9 x 15 px
        $scale = 3;
        $lineHeight = imagefontheight($font);
        $blockWidth = 0;

        foreach ($lines as $line) {
            $blockWidth = max($blockWidth, imagefontwidth($font) * strlen($line));
        }

        $blockHeight = $lineHeight * count($lines);

        $text = imagecreatetruecolor(max($blockWidth, 1), max($blockHeight, 1));
        $white = imagecolorallocate($text, 255, 255, 255);
        imagefilledrectangle($text, 0, 0, $blockWidth, $blockHeight, $white);
        $ink = imagecolorallocate($text, $r, $g, $b);

        foreach ($lines as $i => $line) {
            $x = (int) (($blockWidth - imagefontwidth($font) * strlen($line)) / 2);
            imagestring($text, $font, $x, $i * $lineHeight, $line, $ink);
        }

        $targetW = min($size - 200, $blockWidth * $scale);
        $targetH = (int) ($blockHeight * ($targetW / max($blockWidth, 1)));

        imagecopyresampled(
            $canvas, $text,
            (int) (($size - $targetW) / 2), (int) (($size - $targetH) / 2),
            0, 0,
            $targetW, $targetH, $blockWidth, $blockHeight,
        );

        imagedestroy($text);
    }

    /** @return array<int, string> */
    private function wrap(string $text, int $width): array
    {
        // Les polices internes de GD sont en ASCII : on translittère les accents.
        $ascii = Str::ascii($text);

        return explode("\n", wordwrap(strtoupper($ascii), $width, "\n", true));
    }

    /** @return array<int, array<string, mixed>> */
    private function services(): array
    {
        return [
            ['name' => 'Installation TV murale',          'category' => 'electronique',   'price' => 250000, 'description' => "Fixation murale de votre téléviseur, réglage de l'image et raccordement des périphériques. Support inclus."],
            ['name' => 'Livraison express Conakry',       'category' => 'accessoires',    'price' => 50000,  'description' => 'Livraison le jour même dans Conakry pour toute commande passée avant 12h.'],
            ['name' => 'Réparation smartphone',           'category' => 'electronique',   'price' => 180000, 'description' => "Diagnostic complet et remplacement d'écran ou de batterie. Intervention en 48h, garantie 3 mois."],
            ['name' => 'Montage de meubles à domicile',   'category' => 'maison-cuisine', 'price' => 150000, 'description' => 'Assemblage de vos meubles chez vous par un technicien, outillage fourni.'],
            ['name' => 'Coiffure à domicile',             'category' => 'beaute-sante',   'price' => 200000, 'description' => 'Prestation de coiffure à votre domicile, sur rendez-vous, produits inclus.'],
            ['name' => 'Nettoyage complet appartement',   'category' => 'maison-cuisine', 'price' => 350000, 'description' => 'Nettoyage en profondeur de votre logement, du sol au plafond, par une équipe de deux personnes.'],
            ['name' => 'Formation bureautique',           'category' => 'informatique',   'price' => 400000, 'description' => "Dix heures de formation Word, Excel et messagerie, en individuel ou en petit groupe."],
            ['name' => 'Photographie événementielle',     'category' => 'accessoires',    'price' => 900000, 'description' => "Couverture photo de votre événement, retouche et livraison des fichiers en haute définition."],
            ['name' => 'Maintenance informatique',        'category' => 'informatique',   'price' => 300000, 'description' => 'Nettoyage, mise à jour et sauvegarde de votre ordinateur. Contrat trimestriel possible.'],
            ['name' => 'Assistance déménagement',         'category' => 'maison-cuisine', 'price' => 750000, 'description' => 'Emballage, transport et remise en place de vos affaires dans Conakry et sa périphérie.'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function products(): array
    {
        $sizes  = fn (int $base) => [
            ['name' => 'Taille S',  'attributes' => ['taille' => 'S'],  'price' => $base,           'stock' => 8],
            ['name' => 'Taille M',  'attributes' => ['taille' => 'M'],  'price' => $base,           'stock' => 14],
            ['name' => 'Taille L',  'attributes' => ['taille' => 'L'],  'price' => $base + 20000,   'stock' => 10],
            ['name' => 'Taille XL', 'attributes' => ['taille' => 'XL'], 'price' => $base + 35000,   'stock' => 6],
        ];

        $storages = fn (int $base) => [
            ['name' => '128 Go', 'attributes' => ['stockage' => '128 Go'], 'price' => $base,            'stock' => 9],
            ['name' => '256 Go', 'attributes' => ['stockage' => '256 Go'], 'price' => $base + 450000,   'stock' => 6],
            ['name' => '512 Go', 'attributes' => ['stockage' => '512 Go'], 'price' => $base + 1100000,  'stock' => 3],
        ];

        $shoes = fn (int $base) => [
            ['name' => 'Pointure 40', 'attributes' => ['pointure' => '40'], 'price' => $base, 'stock' => 7],
            ['name' => 'Pointure 41', 'attributes' => ['pointure' => '41'], 'price' => $base, 'stock' => 9],
            ['name' => 'Pointure 42', 'attributes' => ['pointure' => '42'], 'price' => $base, 'stock' => 11],
            ['name' => 'Pointure 43', 'attributes' => ['pointure' => '43'], 'price' => $base, 'stock' => 5],
        ];

        return [
            // ── Produits simples (25) ─────────────────────────────────────
            ['name' => 'Ventilateur sur pied 16 pouces',   'category' => 'maison-cuisine', 'price' => 420000,  'stock' => 30, 'description' => 'Trois vitesses, oscillation et hauteur réglable. Moteur silencieux basse consommation.'],
            ['name' => 'Bouilloire électrique 1,7 L',      'category' => 'maison-cuisine', 'price' => 185000,  'stock' => 45, 'description' => 'Arrêt automatique, base rotative et filtre anticalcaire amovible.'],
            ['name' => 'Blender chauffant 1,5 L',          'category' => 'maison-cuisine', 'price' => 680000,  'stock' => 18, 'description' => 'Six lames en inox, fonction soupe chaude et bol en verre résistant.'],
            ['name' => 'Fer à repasser vapeur',            'category' => 'maison-cuisine', 'price' => 265000,  'stock' => 26, 'description' => 'Semelle céramique, jet de vapeur vertical et système anti-goutte.'],
            ['name' => 'Cafetière filtre 12 tasses',       'category' => 'maison-cuisine', 'price' => 340000,  'stock' => 20, 'description' => 'Verseuse en verre graduée, maintien au chaud et filtre permanent lavable.'],
            ['name' => 'Batterie de cuisine 8 pièces',     'category' => 'maison-cuisine', 'price' => 890000,  'stock' => 12, 'description' => 'Casseroles et poêles en inox avec fond diffuseur, compatibles tous feux.'],
            ['name' => 'Chargeur rapide 65 W USB-C',       'category' => 'electronique',   'price' => 220000,  'stock' => 60, 'description' => 'Trois ports, charge simultanée ordinateur portable et téléphone.'],
            ['name' => 'Batterie externe 20 000 mAh',      'category' => 'electronique',   'price' => 380000,  'stock' => 40, 'description' => 'Charge rapide, écran de niveau et double sortie USB.'],
            ['name' => 'Enceinte Bluetooth étanche',       'category' => 'electronique',   'price' => 450000,  'stock' => 22, 'description' => 'Douze heures d\'autonomie, certification IPX7 et micro intégré pour les appels.'],
            ['name' => 'Casque audio sans fil',            'category' => 'electronique',   'price' => 620000,  'stock' => 17, 'description' => 'Réduction de bruit active, coussinets mémoire de forme et étui de transport.'],
            ['name' => 'Barre de son 2.1',                 'category' => 'electronique',   'price' => 1250000, 'stock' => 9,  'description' => 'Caisson de basses sans fil, entrée HDMI ARC et mode dialogue.'],
            ['name' => 'Télévision LED 43 pouces',         'category' => 'electronique',   'price' => 2850000, 'stock' => 7,  'description' => 'Dalle Full HD, deux ports HDMI et récepteur TNT intégré.'],
            ['name' => 'Souris sans fil ergonomique',      'category' => 'informatique',   'price' => 145000,  'stock' => 55, 'description' => 'Capteur haute précision, molette silencieuse et autonomie de douze mois.'],
            ['name' => 'Clavier mécanique compact',        'category' => 'informatique',   'price' => 390000,  'stock' => 24, 'description' => 'Switches tactiles, rétroéclairage réglable et repose-poignets.'],
            ['name' => 'Disque SSD externe 1 To',          'category' => 'informatique',   'price' => 980000,  'stock' => 15, 'description' => 'Lecture jusqu\'à 1 050 Mo/s, boîtier aluminium et câble USB-C fourni.'],
            ['name' => 'Routeur Wi-Fi 6 double bande',     'category' => 'informatique',   'price' => 720000,  'stock' => 13, 'description' => 'Quatre antennes, contrôle parental et gestion depuis l\'application mobile.'],
            ['name' => 'Webcam Full HD',                   'category' => 'informatique',   'price' => 285000,  'stock' => 28, 'description' => 'Capteur 1080p, correction de lumière automatique et double micro.'],
            ['name' => 'Sac à dos ordinateur 15 pouces',   'category' => 'accessoires',    'price' => 310000,  'stock' => 32, 'description' => 'Compartiment matelassé, tissu déperlant et port USB de recharge.'],
            ['name' => 'Montre connectée sport',           'category' => 'accessoires',    'price' => 540000,  'stock' => 19, 'description' => 'Suivi du sommeil et du rythme cardiaque, autonomie de sept jours.'],
            ['name' => 'Lunettes de soleil polarisées',    'category' => 'accessoires',    'price' => 175000,  'stock' => 38, 'description' => 'Verres polarisés catégorie 3, monture légère et étui rigide.'],
            ['name' => 'Parapluie pliant renforcé',        'category' => 'accessoires',    'price' => 95000,   'stock' => 50, 'description' => 'Armature dix baleines résistante au vent, ouverture et fermeture automatiques.'],
            ['name' => 'Beurre de karité pur 500 g',       'category' => 'beaute-sante',   'price' => 75000,   'stock' => 70, 'description' => 'Karité brut non raffiné, récolté et pressé en Haute-Guinée.'],
            ['name' => 'Savon noir africain artisanal',    'category' => 'beaute-sante',   'price' => 45000,   'stock' => 85, 'description' => 'Fabriqué à partir de cendres de cabosses et d\'huile de palmiste.'],
            ['name' => 'Huile de coco pressée à froid',    'category' => 'beaute-sante',   'price' => 88000,   'stock' => 44, 'description' => 'Usage cosmétique et alimentaire, flacon ambré de 500 ml.'],
            ['name' => 'Tondeuse cheveux et barbe',        'category' => 'beaute-sante',   'price' => 295000,  'stock' => 21, 'description' => 'Huit sabots, lame céramique et deux heures d\'autonomie sans fil.'],

            // ── Produits variables (15) ───────────────────────────────────
            ['name' => 'Smartphone Tecno Spark 20',        'category' => 'electronique',   'description' => 'Écran 6,6 pouces, triple capteur photo et batterie 5 000 mAh.',           'variants' => $storages(1250000)],
            ['name' => 'Smartphone Samsung Galaxy A35',    'category' => 'electronique',   'description' => 'Écran AMOLED, résistance à l\'eau et quatre ans de mises à jour.',        'variants' => $storages(2450000)],
            ['name' => 'Tablette 10 pouces',               'category' => 'electronique',   'description' => 'Idéale pour la lecture et les cours en ligne, avec support clavier.',      'variants' => $storages(1450000)],
            ['name' => 'Ordinateur portable 15 pouces',    'category' => 'informatique',   'description' => 'Processeur récent, clavier AZERTY rétroéclairé et châssis aluminium.',     'variants' => $storages(4900000)],
            ['name' => 'Ensemble en pagne wax premium',    'category' => 'mode-vetements', 'description' => 'Deux pièces confectionnées à la main en wax véritable, coton certifié.',  'variants' => $sizes(450000)],
            ['name' => 'Boubou brodé homme',               'category' => 'mode-vetements', 'description' => 'Broderie main sur bazin riche, coupe traditionnelle ample.',              'variants' => $sizes(680000)],
            ['name' => 'Robe en bazin riche',              'category' => 'mode-vetements', 'description' => 'Bazin teint à la main, finitions soignées et doublure coton.',            'variants' => $sizes(720000)],
            ['name' => 'Chemise en lin',                   'category' => 'mode-vetements', 'description' => 'Lin lavé respirant, coupe droite, idéale par forte chaleur.',             'variants' => $sizes(260000)],
            ['name' => 'T-shirt coton bio',                'category' => 'mode-vetements', 'description' => 'Coton biologique 180 g, col renforcé et coupe unisexe.',                  'variants' => $sizes(95000)],
            ['name' => 'Jean slim stretch',                'category' => 'mode-vetements', 'description' => 'Denim extensible confortable, cinq poches, coupe ajustée.',               'variants' => $sizes(310000)],
            ['name' => 'Veste légère mi-saison',           'category' => 'mode-vetements', 'description' => 'Coupe-vent déperlant avec doublure filet et capuche repliable.',          'variants' => $sizes(420000)],
            ['name' => 'Chaussures en cuir Peul',          'category' => 'mode-vetements', 'description' => 'Cuir tanné et cousu main par des artisans de Labé.',                      'variants' => $shoes(310000)],
            ['name' => 'Baskets running',                  'category' => 'mode-vetements', 'description' => 'Semelle amortissante, tige respirante et maintien renforcé du talon.',    'variants' => $shoes(480000)],
            ['name' => 'Sandales en cuir',                 'category' => 'mode-vetements', 'description' => 'Cuir souple non doublé, semelle antidérapante, fabrication locale.',      'variants' => $shoes(185000)],
            ['name' => 'Bottines chelsea',                 'category' => 'mode-vetements', 'description' => 'Cuir pleine fleur, élastiques latéraux et semelle cousue.',               'variants' => $shoes(590000)],
        ];
    }
};
