<?php

namespace App\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeScraper
{
    private const API_URL      = 'https://api.anthropic.com/v1/messages';
    private const MODEL        = 'claude-haiku-4-5-20251001';
    private const MAX_HTML_LEN = 80_000;

    private const AMAZON_DOMAINS = [
        'amazon.com', 'amazon.fr', 'amazon.co.uk', 'amazon.de',
        'amazon.it', 'amazon.es', 'amazon.ca', 'amazon.com.br',
        'amazon.co.jp', 'amazon.in', 'amazon.com.au',
    ];

    private const SHEIN_DOMAINS = [
        'shein.com', 'shein.fr', 'shein.co.uk', 'shein.de',
        'shein.es', 'shein.it', 'sheglam.com', 'romwe.com',
    ];

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.key', '');
    }

    // ─── Points d'entrée ──────────────────────────────────────────────

    public function scrape(string $url): array
    {
        $strategy = $this->detectStrategyFromUrl($url);

        // Fetch HTML + headers en une seule requête
        $response = $this->fetchResponse($url, $strategy);
        if (!$response) return [];

        // Shopify détecté via headers → API directe ; si indisponible (Hydrogen/headless), fallback JSON-LD
        if ($this->isShopifyResponse($response)) {
            $products = $this->scrapeShopify($url);
            if (!empty($products)) return $products;
        }

        $html = $response->body();

        // JSON-LD en premier (Amazon, Fashion Nova Hydrogen, tout site standard)
        // allowBatchFetch=true : autorise le fetch des pages produit individuelles pour les CollectionPage
        $products = $this->extractJsonLd($html, $url, true);
        if (!empty($products)) return $products;

        return $this->extractWithClaude($html, $url, $strategy);
    }

    public function scrapeHtml(string $html, string $sourceUrl = ''): array
    {
        $strategy = $this->detectStrategyFromUrl($sourceUrl);

        // JSON-LD d'abord (Amazon et tout site standard)
        // allowBatchFetch=true : pour les CollectionPage (ex: Fashion Nova), va chercher
        // les images sur chaque page produit via l'API Shopify si disponible.
        $products = $this->extractJsonLd($html, $sourceUrl, true);
        if (!empty($products)) return $products;

        return $this->extractWithClaude($html, $sourceUrl, $strategy);
    }

    // ─── Détection de stratégie ───────────────────────────────────────

    private function detectStrategyFromUrl(string $url): string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        foreach (self::AMAZON_DOMAINS as $domain) {
            if (str_contains($host, $domain)) return 'amazon';
        }

        foreach (self::SHEIN_DOMAINS as $domain) {
            if (str_contains($host, $domain)) return 'shein';
        }

        return 'generic';
    }

    private function isShopifyResponse(\Illuminate\Http\Client\Response $response): bool
    {
        $poweredBy = strtolower($response->header('powered-by') ?? '');
        $link      = strtolower($response->header('link') ?? '');
        $xShopify  = $response->header('x-shopid') ?? $response->header('x-shopify-stage') ?? '';

        return str_contains($poweredBy, 'shopify')
            || str_contains($link, 'cdn.shopify.com')
            || !empty($xShopify);
    }

    // ─── Stratégie Shopify ────────────────────────────────────────────

    private function scrapeShopify(string $url): array
    {
        $parsed = parse_url($url);
        $base   = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');
        $path   = $parsed['path'] ?? '';

        // Page produit unique
        if (preg_match('~/products/([^/?#]+)~', $path, $m)) {
            return $this->fetchShopifyProduct($base, $m[1]);
        }

        // Collection spécifique
        if (preg_match('~/collections/([^/?#]+)~', $path, $m)) {
            $apiUrl = $base . '/collections/' . $m[1] . '/products.json?limit=50';
        } else {
            $apiUrl = $base . '/products.json?limit=50';
        }

        return $this->fetchShopifyCollection($apiUrl, $base);
    }

    private function fetchShopifyCollection(string $apiUrl, string $base): array
    {
        try {
            $response = $this->http()->timeout(15)->get($apiUrl);

            if (!$response->successful()) {
                // Fallback vers /products.json global
                $fallback = $base . '/products.json?limit=50';
                $response = $this->http()->timeout(15)->get($fallback);
                if (!$response->successful()) return [];
            }

            $data = $response->json();
            if (empty($data['products'])) return [];

            return array_map(fn($p) => $this->normalizeShopifyProduct($p, $base), $data['products']);
        } catch (\Throwable $e) {
            Log::warning('ClaudeScraper[Shopify]: collection fetch failed', ['url' => $apiUrl, 'error' => $e->getMessage()]);
            return [];
        }
    }

    private function fetchShopifyProduct(string $base, string $handle): array
    {
        try {
            $response = $this->http()->timeout(15)->get("{$base}/products/{$handle}.json");
            if (!$response->successful()) return [];

            $p = $response->json('product');
            if (!$p) return [];

            return [$this->normalizeShopifyProduct($p, $base)];
        } catch (\Throwable $e) {
            Log::warning('ClaudeScraper[Shopify]: product fetch failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function normalizeShopifyProduct(array $p, string $base): array
    {
        $firstVariant = $p['variants'][0] ?? [];
        $image        = $p['images'][0]['src'] ?? null;
        $price        = isset($firstVariant['price']) ? (float) $firstVariant['price'] : null;
        $compare      = isset($firstVariant['compare_at_price']) && $firstVariant['compare_at_price']
            ? (float) $firstVariant['compare_at_price'] : null;

        // Map variant_id → image src
        $variantImageMap = [];
        foreach ($p['images'] ?? [] as $img) {
            foreach ($img['variant_ids'] ?? [] as $vid) {
                $variantImageMap[$vid] = $img['src'];
            }
        }

        // Extract option names (Color, Size…)
        $optionNames = array_column($p['options'] ?? [], 'name');

        $variants = [];
        foreach ($p['variants'] ?? [] as $v) {
            $attributes = [];
            foreach ($optionNames as $i => $optName) {
                $val = $v['option' . ($i + 1)] ?? null;
                if ($val && strtolower($val) !== 'default title') {
                    $attributes[$optName] = $val;
                }
            }
            if (empty($attributes)) continue; // produit simple "Default Title"

            $variants[] = [
                'name'       => implode(' / ', array_values($attributes)),
                'price'      => isset($v['price']) ? (float) $v['price'] : $price,
                'image_url'  => $variantImageMap[$v['id'] ?? ''] ?? $image,
                'attributes' => $attributes,
            ];
        }

        return [
            'id'             => md5(($p['title'] ?? '') . $base . '/products/' . ($p['handle'] ?? '')),
            'name'           => $this->cleanText($p['title'] ?? null),
            'price'          => $price,
            'original_price' => $compare && $compare > $price ? $compare : null,
            'currency'       => null,
            'image_url'      => $image,
            'source_url'     => $base . '/products/' . ($p['handle'] ?? ''),
            'description'    => $this->cleanText(strip_tags($p['body_html'] ?? '')),
            'brand'          => $this->cleanText($p['vendor'] ?? null),
            'rating'         => null,
            'reviews_count'  => null,
            'variants'       => $variants,
        ];
    }

    // ─── Stratégie JSON-LD (Amazon + tout site standard) ─────────────

    private function extractJsonLd(string $html, string $baseUrl, bool $allowBatchFetch = false): array
    {
        $products = [];

        preg_match_all(
            '/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is',
            $html,
            $matches
        );

        foreach ($matches[1] as $json) {
            $data = json_decode(trim($json), true);
            if (!$data) continue;

            $nodes = isset($data['@graph']) ? $data['@graph'] : [$data];

            foreach ($nodes as $node) {
                $type = is_array($node['@type'] ?? '') ? implode(',', $node['@type']) : ($node['@type'] ?? '');

                // ProductGroup (Shopify Hydrogen, variantes)
                if (stripos($type, 'ProductGroup') !== false) {
                    $p = $this->normalizeProductGroup($node, $baseUrl);
                    if ($p) $products[] = $p;
                    continue;
                }

                // Product standard
                if (stripos($type, 'Product') !== false) {
                    $p = $this->normalizeJsonLdProduct($node, $baseUrl);
                    if ($p) $products[] = $p;
                    continue;
                }

                // CollectionPage (ex: Fashion Nova) → mainEntity > ItemList > ListItem
                if (stripos($type, 'CollectionPage') !== false && !empty($node['mainEntity']['itemListElement'])) {
                    $items = $node['mainEntity']['itemListElement'];
                    if ($allowBatchFetch) {
                        return $this->batchFetchProductPages($items, $baseUrl);
                    }
                    // Sans batch : retourner nom + URL seulement
                    return $this->normalizeListItems($items, $baseUrl);
                }

                // ItemList direct contenant des produits
                if (stripos($type, 'ItemList') !== false && !empty($node['itemListElement'])) {
                    foreach ($node['itemListElement'] as $el) {
                        $inner     = $el['item'] ?? $el;
                        $innerType = is_array($inner['@type'] ?? '') ? implode(',', $inner['@type']) : ($inner['@type'] ?? '');
                        if (stripos($innerType, 'ProductGroup') !== false) {
                            $p = $this->normalizeProductGroup($inner, $baseUrl);
                            if ($p) $products[] = $p;
                        } elseif (stripos($innerType, 'Product') !== false) {
                            $p = $this->normalizeJsonLdProduct($inner, $baseUrl);
                            if ($p) $products[] = $p;
                        }
                    }
                }
            }
        }

        return $products;
    }

    private function normalizeJsonLdProduct(array $node, string $baseUrl): ?array
    {
        $name = $this->cleanText($node['name'] ?? null);
        if (!$name) return null;

        [$price, $originalPrice, $currency] = $this->extractOfferPrice($node['offers'] ?? null);

        $image     = $this->extractImage($node['image'] ?? null);
        $rating    = $this->toFloat($node['aggregateRating']['ratingValue'] ?? null);
        $reviews   = isset($node['aggregateRating']['reviewCount']) ? (int) $node['aggregateRating']['reviewCount'] : null;
        $brand     = is_array($node['brand'] ?? null) ? ($node['brand']['name'] ?? null) : ($node['brand'] ?? null);
        $sourceUrl = $this->absoluteUrl($node['url'] ?? null, $baseUrl) ?? $baseUrl;

        return [
            'id'             => md5($name . $sourceUrl),
            'name'           => $name,
            'price'          => $price,
            'original_price' => $originalPrice && $price && $originalPrice > $price ? $originalPrice : null,
            'currency'       => $currency,
            'image_url'      => $this->absoluteUrl($image, $baseUrl),
            'source_url'     => $sourceUrl,
            'description'    => $this->cleanText($node['description'] ?? null),
            'brand'          => $this->cleanText($brand),
            'rating'         => $rating,
            'reviews_count'  => $reviews,
        ];
    }

    private function normalizeProductGroup(array $node, string $baseUrl): ?array
    {
        $name = $this->cleanText($node['name'] ?? null);
        if (!$name) return null;

        $rawVariants  = $node['hasVariant'] ?? [];
        $firstVariant = $rawVariants[0] ?? null;

        [$price, $originalPrice, $currency] = $this->extractOfferPrice(
            $node['offers'] ?? $firstVariant['offers'] ?? null
        );

        $image = $this->extractImage($node['image'] ?? null)
              ?? $this->extractImage($firstVariant['image'] ?? null);

        $brand     = is_array($node['brand'] ?? null) ? ($node['brand']['name'] ?? null) : ($node['brand'] ?? null);
        $sourceUrl = $this->absoluteUrl($node['url'] ?? null, $baseUrl) ?? $baseUrl;

        // Extraire toutes les variantes (couleurs, tailles…)
        $variants = [];
        foreach ($rawVariants as $v) {
            $vName = $this->cleanText($v['name'] ?? null);
            if (!$vName) continue;

            [$vPrice] = $this->extractOfferPrice($v['offers'] ?? null);
            $vImg     = $this->absoluteUrl($this->extractImage($v['image'] ?? null), $baseUrl);

            $variants[] = [
                'name'       => $vName,
                'price'      => $vPrice ?? $price,
                'image_url'  => $vImg ?? $this->absoluteUrl($image, $baseUrl),
                'attributes' => $this->guessAttributes($vName),
            ];
        }

        return [
            'id'             => md5($name . $sourceUrl),
            'name'           => $name,
            'price'          => $price,
            'original_price' => $originalPrice && $price && $originalPrice > $price ? $originalPrice : null,
            'currency'       => $currency,
            'image_url'      => $this->absoluteUrl($image, $baseUrl),
            'source_url'     => $sourceUrl,
            'description'    => $this->cleanText($node['description'] ?? $firstVariant['description'] ?? null),
            'brand'          => $this->cleanText($brand),
            'rating'         => null,
            'reviews_count'  => null,
            'variants'       => $variants,
        ];
    }

    private function normalizeListItems(array $listItems, string $baseUrl): array
    {
        $products = [];
        foreach ($listItems as $item) {
            $name = $this->cleanText($item['name'] ?? null);
            $url  = $this->absoluteUrl($item['url'] ?? null, $baseUrl);
            if (!$name || !$url) continue;

            $products[] = [
                'id'             => md5($name . $url),
                'name'           => $name,
                'price'          => null,
                'original_price' => null,
                'currency'       => null,
                'image_url'      => null,
                'source_url'     => $url,
                'description'    => null,
                'brand'          => null,
                'rating'         => null,
                'reviews_count'  => null,
            ];
        }
        return $products;
    }

    private function batchFetchProductPages(array $listItems, string $baseUrl, int $limit = 10): array
    {
        $products = [];
        $count    = 0;

        foreach ($listItems as $item) {
            if ($count >= $limit) break;

            $url = $this->absoluteUrl($item['url'] ?? null, $baseUrl);
            if (!$url || !str_starts_with($url, 'http')) continue;

            try {
                $found = [];

                // Pour les stores Shopify standards, l'API .json évite de parser le HTML.
                // Pour les stores headless (Hydrogen, etc.) l'API retourne 404 → fallback HTML.
                if (preg_match('~^(https?://[^/]+)/products/([^/?#]+)~', $url, $m)) {
                    $found = $this->fetchShopifyProduct($m[1], $m[2]);
                }

                if (empty($found)) {
                    $resp = $this->http()->timeout(8)->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/124.0.0.0 Safari/537.36',
                    ])->get($url);

                    if (!$resp->successful()) continue;
                    $found = $this->extractJsonLd($resp->body(), $url, false);
                }

                if (!empty($found)) {
                    $products = array_merge($products, $found);
                    $count++;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $products;
    }

    // ─── Helpers d'extraction JSON-LD ────────────────────────────────

    private function extractOfferPrice(mixed $offers): array
    {
        if (!$offers) return [null, null, null];

        $offer    = isset($offers[0]) ? $offers[0] : $offers;
        $price    = $this->toFloat($offer['price'] ?? $offer['lowPrice'] ?? null);
        $currency = $offer['priceCurrency'] ?? null;
        $original = !empty($offer['highPrice']) ? $this->toFloat($offer['highPrice']) : null;

        return [$price, $original, $currency];
    }

    private function extractImage(mixed $image): ?string
    {
        if (!$image) return null;
        if (is_string($image)) return $image;
        if (is_array($image)) {
            return $image['url'] ?? (is_string(reset($image)) ? reset($image) : null);
        }
        return null;
    }

    // ─── Fetch HTML ───────────────────────────────────────────────────

    private function fetchResponse(string $url, string $strategy = 'generic'): ?\Illuminate\Http\Client\Response
    {
        try {
            $response = $this->http()
                ->timeout(20)
                ->withHeaders($this->headersFor($strategy))
                ->get($url);

            return $response->successful() ? $response : null;
        } catch (\Throwable $e) {
            Log::warning('ClaudeScraper: fetch failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function headersFor(string $strategy): array
    {
        $common = [
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept-Encoding' => 'gzip, deflate',
            'Cache-Control'   => 'no-cache',
        ];

        return match ($strategy) {
            // Shein : user-agent mobile pour contourner les protections desktop
            'shein' => array_merge($common, [
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1',
            ]),
            // Amazon : Chrome desktop, langue neutre pour ne pas forcer une locale
            'amazon' => array_merge($common, [
                'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ]),
            default => array_merge($common, [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            ]),
        };
    }

    // ─── Nettoyage HTML ───────────────────────────────────────────────

    private function cleanHtml(string $html, string $strategy = 'generic'): string
    {
        // Supprimer les éléments non-contenu
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<svg\b[^>]*>.*?<\/svg>/is', '', $html);
        $html = preg_replace('/<!--.*?-->/s', '', $html);
        $html = preg_replace('/<(nav|footer|header|aside|noscript|iframe|form|button)[^>]*>.*?<\/\1>/is', '', $html);

        // Nettoyage spécifique Amazon : extraire la zone produit pertinente
        if ($strategy === 'amazon') {
            // Page produit individuelle
            if (preg_match('/<div[^>]+id=["\']dp["\'][^>]*>(.*)/is', $html, $m)) {
                $html = $m[1];
            }
            // Page listing/bestsellers/recherche : zone principale de résultats
            elseif (preg_match('/<div[^>]+id=["\'](?:zg-ordered-list|search|s-results-list-atf)["\'][^>]*>(.*?)<\/div>\s*<div[^>]+id=["\'](?:zg-right-col|rhf|navFooter)/is', $html, $m)) {
                $html = $m[1];
            }
            // Fallback listing : chercher le div conteneur des cartes produit
            elseif (preg_match('/<ol[^>]+class=["\'][^"\']*zg[^"\']*["\'][^>]*>(.*?)<\/ol>/is', $html, $m)) {
                $html = $m[1];
            }
        }

        // Nettoyage spécifique Shein : supprimer les zones de navigation et filtres
        if ($strategy === 'shein') {
            $html = preg_replace('/<div[^>]+class=["\'][^"\']*(?:she-header|filter-bar|sidebar|footer)[^"\']*["\'][^>]*>.*?<\/div>/is', '', $html);
        }

        // Lazy-loading : promouvoir data-src / data-original / data-lazy-src → src
        $html = preg_replace_callback(
            '/<img\b([^>]*?)>/is',
            function (array $m) {
                $tag = $m[1];
                // Extraire la vraie URL depuis data-src, data-original, data-lazy-src, data-srcset…
                if (preg_match('/\bdata-(?:src|original|lazy-src|bg)\s*=\s*["\']([^"\']+)["\']/', $tag, $lazy)) {
                    // Remplacer ou injecter src
                    if (preg_match('/\bsrc\s*=\s*["\'][^"\']*["\']/', $tag)) {
                        $tag = preg_replace('/\bsrc\s*=\s*["\'][^"\']*["\']/', 'src="' . $lazy[1] . '"', $tag);
                    } else {
                        $tag = 'src="' . $lazy[1] . '" ' . $tag;
                    }
                }
                return '<img' . $tag . '>';
            },
            $html
        );

        $html = preg_replace('/\s{2,}/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);

        return trim($html);
    }

    // ─── Extraction via Claude ────────────────────────────────────────

    private function extractWithClaude(string $html, string $url, string $strategy = 'generic'): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Clé API Anthropic non configurée (ANTHROPIC_API_KEY).');
        }

        $clean = $this->cleanHtml($html, $strategy);

        if (strlen($clean) > self::MAX_HTML_LEN) {
            $clean = substr($clean, 0, self::MAX_HTML_LEN) . "\n[HTML tronqué]";
        }

        $response = Http::withOptions([
            'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4],
        ])->withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(60)->post(self::API_URL, [
            'model'      => self::MODEL,
            'max_tokens' => 4096,
            'messages'   => [[
                'role'    => 'user',
                'content' => $this->buildPrompt($clean, $url, $strategy),
            ]],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Erreur API Claude : ' . $response->body());
        }

        return $this->normalize($response->json('content.0.text', ''), $url);
    }

    private function buildPrompt(string $html, string $pageUrl, string $strategy = 'generic'): string
    {
        $hint = match ($strategy) {
            'amazon' => "C'est une page Amazon. Les prix sont dans des éléments avec classe 'a-price'. Les images dans 'landingImage'. La marque dans 'bylineInfo' ou 'productByline'.",
            'shein'  => "C'est une page Shein (mode). Les produits sont dans des blocs répétitifs (product-card, goods-item ou similaire). Les prix peuvent avoir un format local.",
            default  => '',
        };

        $hintLine = $hint ? "\nContexte site : {$hint}\n" : '';

        return <<<PROMPT
Tu es un expert en extraction de données e-commerce. Tu reçois le HTML d'une page web.
{$hintLine}
Ton objectif : identifier et extraire TOUS les produits présents sur cette page, avec leurs déclinaisons (couleurs, tailles, etc.) si disponibles.

Analyse intelligemment : repère les blocs répétitifs (cartes produit, listings, grilles), les sélecteurs de taille/couleur, les tableaux de variantes. Raisonne sur la sémantique du contenu, pas sur des noms de classes fixes.

Retourne UNIQUEMENT un tableau JSON valide, sans markdown ni explication :

[
  {
    "name": "Nom complet du produit (texte brut, sans HTML ni entités)",
    "price": 29.99,
    "original_price": 49.99,
    "currency": "USD",
    "image_url": "URL absolue de l'image principale",
    "source_url": "URL absolue de la fiche produit",
    "description": "Description courte ou null",
    "brand": "Marque ou null",
    "rating": 4.5,
    "reviews_count": 120,
    "variants": [
      {
        "name": "Rouge / M",
        "price": 29.99,
        "image_url": "URL absolue ou null",
        "attributes": {"Couleur": "Rouge", "Taille": "M"}
      }
    ]
  }
]

Règles :
- name, description, brand : texte brut uniquement, jamais de HTML ni entités HTML (&amp; → &, etc.)
- price et original_price : nombres décimaux (retire symboles monétaires, espaces, virgules)
- URLs relatives → absolues avec cette base : {$pageUrl}
- N'inclus que les vrais produits (pas bannières, publicités, catégories)
- variants : tableau de déclinaisons si le produit a des options (couleurs, tailles, matières…). Mettre [] si pas de déclinaisons détectées.
- Dans variants.attributes, utilise les noms de propriétés détectés (Couleur, Taille, Matière, Style, etc.)
- Retourne [] si aucun produit identifiable
- Champs non trouvés : null

URL de la page : {$pageUrl}

HTML :
{$html}
PROMPT;
    }

    // ─── Normalisation ────────────────────────────────────────────────

    private function normalize(string $text, string $baseUrl): array
    {
        if (preg_match('/\[[\s\S]*\]/m', $text, $m)) {
            $raw = $m[0];
        } else {
            Log::warning('ClaudeScraper: aucun JSON dans la réponse', ['preview' => substr($text, 0, 300)]);
            return [];
        }

        $items = json_decode($raw, true);
        if (!is_array($items)) return [];

        $products = [];
        foreach ($items as $item) {
            if (empty($item['name'])) continue;

            $sourceUrl = $this->absoluteUrl($item['source_url'] ?? null, $baseUrl) ?? $baseUrl;

            $variants = [];
            foreach ($item['variants'] ?? [] as $v) {
                $vName = $this->cleanText($v['name'] ?? null);
                if (!$vName) continue;

                $variants[] = [
                    'name'       => $vName,
                    'price'      => $this->toFloat($v['price'] ?? null),
                    'image_url'  => $this->absoluteUrl($v['image_url'] ?? null, $baseUrl),
                    'attributes' => is_array($v['attributes'] ?? null) ? $v['attributes'] : [],
                ];
            }

            $products[] = [
                'id'             => md5(($item['name'] ?? '') . $sourceUrl),
                'name'           => $this->cleanText($item['name']),
                'price'          => $this->toFloat($item['price'] ?? null),
                'original_price' => $this->toFloat($item['original_price'] ?? null),
                'currency'       => $item['currency'] ?? null,
                'image_url'      => $this->absoluteUrl($item['image_url'] ?? null, $baseUrl),
                'source_url'     => $sourceUrl,
                'description'    => $this->cleanText($item['description'] ?? null),
                'brand'          => $this->cleanText($item['brand'] ?? null),
                'rating'         => $this->toFloat($item['rating'] ?? null),
                'reviews_count'  => isset($item['reviews_count']) ? (int) $item['reviews_count'] : null,
                'variants'       => $variants,
            ];
        }

        return $products;
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    private function guessAttributes(string $variantName): array
    {
        static $sizeSet = [
            'XS','S','M','L','XL','XXL','XXXL','2XL','3XL','4XL',
            'XS/S','S/M','M/L','L/XL','ONE SIZE','ONESIZE','TAILLE UNIQUE',
            '34','36','38','40','42','44','46','48','50',
        ];

        $parts     = array_map('trim', explode('/', $variantName));
        $sizeFound = [];
        $colorParts = [];

        foreach ($parts as $part) {
            if (in_array(strtoupper($part), $sizeSet)) {
                $sizeFound[] = $part;
            } else {
                $colorParts[] = $part;
            }
        }

        $attrs = [];
        if (!empty($sizeFound))  $attrs['Taille']  = implode('/', $sizeFound);
        if (!empty($colorParts)) $attrs['Couleur']  = implode('/', $colorParts);

        return $attrs ?: ['Variante' => $variantName];
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withOptions(['verify' => false, 'allow_redirects' => true]);
    }

    private function cleanText(?string $text): ?string
    {
        if ($text === null || $text === '') return null;
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        return trim($text) ?: null;
    }

    private function toFloat(mixed $val): ?float
    {
        if ($val === null || $val === '') return null;
        $cleaned = preg_replace('/[^\d.,]/', '', (string) $val);
        $cleaned = str_replace(',', '.', $cleaned);
        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    private function absoluteUrl(?string $url, string $base): ?string
    {
        if (!$url) return null;

        if (str_starts_with($url, 'http')) {
            // Fix malformed URLs like https://site.com//cdn.other.com/path
            // These occur when an LLM incorrectly prefixes a protocol-relative URL with the page host.
            if (preg_match('#^https?://[^/]+//([a-zA-Z0-9][^/]*/\S*)$#', $url, $m)) {
                return 'https://' . $m[1];
            }
            return $url;
        }

        if (str_starts_with($url, '//')) return 'https:' . $url;

        $parsed = parse_url($base);
        $origin = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');

        return $origin . '/' . ltrim($url, '/');
    }
}
