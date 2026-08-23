<?php

namespace App\Services\Admin;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Scrapers\ClaudeScraper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Support\Media;

class ScraperService
{
    private const DISK = 'local';
    private const DIR  = 'scraped';

    public function __construct(
        protected ClaudeScraper $scraper,
        protected MarginService $marginService
    ) {}

    // ─── Scraping ─────────────────────────────────────────────────────

    public function runFromHtml(string $html, string $sourceUrl = '', string $label = ''): string
    {
        $products = $this->scraper->scrapeHtml($html, $sourceUrl ?: 'about:blank');

        $slug     = $label ? Str::slug($label) : ($sourceUrl ? Str::slug(parse_url($sourceUrl, PHP_URL_HOST) ?? 'html') : 'html-paste');
        $filename = now()->format('Ymd_His') . '_' . $slug . '.json';

        $content = json_encode([
            'scraped_at' => now()->toIso8601String(),
            'label'      => $label,
            'url'        => $sourceUrl,
            'count'      => count($products),
            'products'   => $products,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        Storage::disk(self::DISK)->put(self::DIR . '/' . $filename, $content);

        return $filename;
    }

    // ─── Gestion des fichiers ─────────────────────────────────────────

    public function listFiles(): array
    {
        $files = Storage::disk(self::DISK)->files(self::DIR);

        $result = [];
        foreach ($files as $path) {
            if (!str_ends_with($path, '.json')) continue;

            $name     = basename($path);
            $meta     = $this->readMeta($path);

            $result[] = [
                'filename'   => $name,
                'path'       => $path,
                'label'      => $meta['label'] ?? '',
                'url'        => $meta['url'] ?? '',
                'count'      => $meta['count'] ?? 0,
                'scraped_at' => $meta['scraped_at'] ?? null,
                'size'       => Storage::disk(self::DISK)->size($path),
            ];
        }

        // Plus récents en premier
        usort($result, fn($a, $b) => strcmp($b['filename'], $a['filename']));

        return $result;
    }

    public function readFile(string $filename): ?array
    {
        $path = self::DIR . '/' . $filename;

        if (!Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        return json_decode(Storage::disk(self::DISK)->get($path), true);
    }

    public function deleteFile(string $filename): void
    {
        Storage::disk(self::DISK)->delete(self::DIR . '/' . $filename);
    }

    private function readMeta(string $path): array
    {
        try {
            $content = Storage::disk(self::DISK)->get($path);
            $data    = json_decode($content, true);
            return $data ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    // ─── Groupement & révision ────────────────────────────────────────

    /**
     * Regroupe des produits scrapés en groupes (parent + variantes).
     *
     * Deux cas :
     *  1. Le produit a déjà un tableau 'variants' (ProductGroup JSON-LD, Shopify API) → groupe direct.
     *  2. Les produits sont à plat avec un pattern "Nom - Couleur/Taille" → auto-groupement.
     */
    public function groupProducts(array $products): array
    {
        $groups  = [];
        $nameMap = []; // base_name => [items…]

        foreach ($products as $product) {
            if (!empty($product['variants'])) {
                $groups[] = $this->buildGroup($product, 'variable', $product['variants']);
                continue;
            }

            $baseName = $this->detectBaseName($product['name'] ?? '');
            $nameMap[$baseName][] = $product;
        }

        foreach ($nameMap as $baseName => $items) {
            if (count($items) === 1) {
                $groups[] = $this->buildGroup($items[0], 'simple', []);
            } else {
                $variants = [];
                foreach ($items as $item) {
                    $suffix = $this->detectVariantSuffix($item['name'] ?? '', $baseName);
                    $variants[] = [
                        'name'       => $suffix ?? $item['name'],
                        'price'      => $item['price'] ?? null,
                        'image_url'  => $item['image_url'] ?? null,
                        'attributes' => $suffix ? $this->guessAttributesFromName($suffix) : ['Variante' => $item['name']],
                    ];
                }
                $representative = array_merge($items[0], ['name' => $baseName]);
                $groups[] = $this->buildGroup($representative, 'variable', $variants);
            }
        }

        return $groups;
    }

    private function buildGroup(array $product, string $type, array $variants): array
    {
        $supplierPrice = (!empty($product['price']) && $product['price'] > 0)
            ? (float) $product['price'] : null;
        $salePrice = $supplierPrice
            ? $this->marginService->calculateSalePrice($supplierPrice)
            : null;

        $missing = [];
        if (empty($product['name']))  $missing[] = 'name';
        if (!$supplierPrice)          $missing[] = 'supplier_price';
        if (!$salePrice)              $missing[] = 'price';
        if (empty($product['description'])) $missing[] = 'description';
        if (empty($product['image_url']))   $missing[] = 'image_url';

        $critical    = count(array_intersect(['name', 'price'], $missing));
        $nonCritical = count(array_intersect(['description', 'image_url'], $missing));
        $completeness = (int) round((1 - ($critical * 0.35 + $nonCritical * 0.15)) * 100);
        $completeness = max(0, min(100, $completeness));

        // Pré-calculer le prix de vente pour chaque variante
        $enrichedVariants = [];
        foreach ($variants as $v) {
            $vSupplier = (!empty($v['price']) && $v['price'] > 0) ? (float) $v['price'] : $supplierPrice;
            $vSale     = $vSupplier ? $this->marginService->calculateSalePrice($vSupplier) : $salePrice;
            $enrichedVariants[] = array_merge($v, [
                'attributes'     => $this->normalizeAttributeKeys($v['attributes'] ?? []),
                'supplier_price' => $vSupplier,
                'sale_price'     => $vSale,
            ]);
        }

        return [
            'name'           => $product['name'] ?? '',
            'type'           => $type,
            'description'    => $product['description'] ?? null,
            'brand'          => $product['brand'] ?? null,
            'supplier_price' => $supplierPrice,
            'price'          => $salePrice,
            'image_url'      => $product['image_url'] ?? null,
            'source_url'     => $product['source_url'] ?? null,
            'variants'       => $enrichedVariants,
            'missing_fields' => $missing,
            'completeness'   => $completeness,
        ];
    }

    private function detectBaseName(string $name): string
    {
        // Pattern : "Nom du Produit - Couleur/Taille" → retourne "Nom du Produit"
        if (preg_match('/^(.+?)\s+-\s+\S+.*$/', $name, $m)) {
            return trim($m[1]);
        }
        return $name;
    }

    private function detectVariantSuffix(string $name, string $baseName): ?string
    {
        $escaped = preg_quote($baseName, '/');
        if (preg_match('/^' . $escaped . '\s+-\s+(.+)$/', $name, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function normalizeAttributeKeys(array $attributes): array
    {
        static $map = [
            'color'    => 'Couleur', 'colour' => 'Couleur', 'couleur' => 'Couleur',
            'size'     => 'Taille',  'taille' => 'Taille',  'talla'   => 'Taille',
            'größe'    => 'Taille',  'größen' => 'Taille',  'misura'  => 'Taille',
            'material' => 'Matière', 'matière'=> 'Matière', 'materia' => 'Matière',
            'style'    => 'Style',   'modèle' => 'Modèle',  'model'   => 'Modèle',
        ];

        $normalized = [];
        foreach ($attributes as $key => $value) {
            $normalized[$map[strtolower((string) $key)] ?? $key] = $value;
        }
        return $normalized;
    }

    private function guessAttributesFromName(string $variantName): array
    {
        static $sizeSet = [
            'XS','S','M','L','XL','XXL','XXXL','2XL','3XL','4XL',
            'XS/S','S/M','M/L','L/XL','ONE SIZE','ONESIZE','TAILLE UNIQUE',
            '34','36','38','40','42','44','46','48','50',
        ];

        $parts      = array_map('trim', explode('/', $variantName));
        $sizeParts  = [];
        $colorParts = [];

        foreach ($parts as $part) {
            if (in_array(strtoupper($part), $sizeSet)) {
                $sizeParts[] = $part;
            } else {
                $colorParts[] = $part;
            }
        }

        $attrs = [];
        if (!empty($sizeParts))  $attrs['Taille']  = implode('/', $sizeParts);
        if (!empty($colorParts)) $attrs['Couleur']  = implode('/', $colorParts);

        return $attrs ?: ['Variante' => $variantName];
    }

    // ─── Création de produits ─────────────────────────────────────────

    /**
     * Crée des produits depuis les groupes validés dans la vue de révision.
     */
    public function createProductGroups(array $groups, Supplier $supplier): int
    {
        $created = 0;

        foreach ($groups as $group) {
            $isVariable   = ($group['type'] ?? 'simple') === 'variable' && !empty($group['variants']);
            $type         = $isVariable ? Product::TYPE_VARIABLE : Product::TYPE_SIMPLE;
            $supplierPrice = !empty($group['supplier_price']) ? (float) $group['supplier_price'] : null;
            $salePrice     = !empty($group['price'])          ? (float) $group['price']          : null;

            if (!$salePrice && $supplierPrice) {
                $salePrice = $this->marginService->calculateSalePrice($supplierPrice);
            }

            $product = Product::create([
                'type'           => $type,
                'name'           => $group['name'],
                'slug'           => $this->uniqueSlug($group['name']),
                'description'    => $group['description'] ?? null,
                'supplier_price' => $supplierPrice,
                'price'          => $isVariable ? null : ($salePrice ?? 0),
                'stock_quantity' => 0,
                'is_active'      => true,
                'is_published'   => false,
                'is_service'     => false,
                'supplier_id'    => $supplier->id,
            ]);

            if ($isVariable) {
                $imgIndex = 0;
                foreach ($group['variants'] as $idx => $v) {
                    $vSupplier = !empty($v['supplier_price']) ? (float) $v['supplier_price'] : $supplierPrice;
                    $vSale     = !empty($v['price'])          ? (float) $v['price']          : null;

                    if (!$vSale && $vSupplier) {
                        $vSale = $this->marginService->calculateSalePrice($vSupplier, $product->id);
                    }

                    $variantName = trim($v['name'] ?? '');

                    $createdVariant = $product->variants()->create([
                        'sku'            => 'V' . strtoupper(substr(md5($product->id . $idx . $variantName), 0, 8)),
                        'name'           => $variantName ?: null,
                        'attributes'     => $v['attributes'] ?? [],
                        'price'          => $vSale ?? 0,
                        'supplier_price' => $vSupplier,
                        'stock_quantity' => 0,
                        'is_active'      => true,
                    ]);

                    // L'image part sur la variante : elle sert de vignette dans le
                    // sélecteur couleur/taille de la fiche publique.
                    if (!empty($v['image_url'])) {
                        $this->attachImage($product, $v['image_url'], $imgIndex++, $createdVariant);
                    }
                }
            } else {
                if ($supplierPrice) {
                    $this->marginService->applyToProduct($product);
                }
                if (!empty($group['image_url'])) {
                    $this->attachImage($product, $group['image_url']);
                }
            }

            $created++;
        }

        return $created;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function attachImage(Product $product, string $imageUrl, int $index = 0, ?ProductVariant $variant = null): void
    {
        try {
            $response = Http::timeout(15)
                ->withOptions(['verify' => false])
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($imageUrl);

            if (!$response->successful()) return;

            $ext  = $this->guessExtension($imageUrl, $response->header('Content-Type') ?? '');
            $slug = $index > 0 ? "scraped_{$product->id}_{$index}" : "scraped_{$product->id}";
            $path = Media::productImages($product->id) . "/{$slug}.{$ext}";

            Storage::disk(Media::diskName())->put($path, $response->body());

            $isPrimary = $product->images()->count() === 0;
            $product->images()->create([
                'variant_id' => $variant?->id,
                'path'       => $path,
                'sort_order' => $index + 1,
                'is_primary' => $isPrimary,
            ]);
        } catch (\Throwable) {
            // Image optionnelle, on continue sans bloquer
        }
    }

    private function guessExtension(string $url, string $contentType): string
    {
        if (str_contains($contentType, 'png'))  return 'png';
        if (str_contains($contentType, 'webp')) return 'webp';
        if (str_contains($contentType, 'gif'))  return 'gif';

        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? $ext : 'jpg';
    }
}
