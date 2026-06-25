<?php

namespace App\Scrapers;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class GenericScraper
{
    private int $timeout     = 20;
    private int $maxProducts = 30;

    /**
     * Extrait les produits depuis n'importe quelle URL.
     *
     * Stratégie :
     *  1. Fetch HTTP classique + extraction (JSON-LD → OG → JS embarqué → patterns HTML)
     *  2. Si ≤1 produit trouvé et que la page contient des liens produit → crawl individuel
     *  3. Si toujours 0 produit → fallback Browsershot (headless Chrome, pour les SPA CSR)
     */
    public function scrape(string $url): array
    {
        $html = $this->fetchHtml($url);
        if (!$html) return [];

        // ── Étape 1 : extraction directe ───────────────────────────────
        $products = $this->extractFromJsonLd($html, $url);

        if (empty($products)) {
            $products = $this->extractFromOpenGraph($html, $url);
        }

        if (empty($products)) {
            $products = $this->extractFromEmbeddedJs($html, $url);
        }

        if (empty($products)) {
            $products = $this->extractFromHtmlPatterns($html, $url);
        }

        // ── Étape 2 : page de résultats → crawl individuel ─────────────
        if (count($products) <= 1) {
            $productUrls = $this->extractProductLinks($html, $url);

            if (count($productUrls) >= 2) {
                $products = $this->crawlProductPages($productUrls);
            }
        }

        // ── Étape 3 : fallback Browsershot (SPA/CSR) ───────────────────
        // Si on n'a toujours rien (ou 1 seul résultat de fallback HTML),
        // on relance avec un vrai navigateur headless qui exécute le JS.
        if (count($products) <= 1) {
            $products = $this->scrapeWithBrowsershot($url);
        }

        return $this->deduplicate($products);
    }

    // ─── HTTP ──────────────────────────────────────────────────────────

    private function fetchHtml(string $url): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'fr-FR,fr;q=0.9,en-US;q=0.8',
                'DNT'             => '1',
            ])
            ->timeout($this->timeout)
            ->withOptions(['verify' => false])
            ->get($url);

            return $response->successful() ? $response->body() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Extraction depuis un HTML fourni directement (copié-collé par l'utilisateur).
     * Bypasse toute protection anti-bot : l'utilisateur apporte lui-même le HTML.
     */
    public function scrapeHtml(string $html, string $sourceUrl): array
    {
        $products = $this->extractFromJsonLd($html, $sourceUrl);

        if (empty($products)) {
            $products = $this->extractFromOpenGraph($html, $sourceUrl);
        }

        if (empty($products)) {
            $products = $this->extractFromEmbeddedJs($html, $sourceUrl);
        }

        if (empty($products)) {
            $products = $this->extractFromRenderedDom($html, $sourceUrl);
        }

        if (empty($products)) {
            $products = $this->extractFromHtmlPatterns($html, $sourceUrl);
        }

        if (count($products) <= 1) {
            $productUrls = $this->extractProductLinks($html, $sourceUrl);
            if (count($productUrls) >= 2) {
                $products = $this->crawlProductPages($productUrls);
            }
        }

        return $this->deduplicate($products);
    }

    // ─── Browsershot fallback (SPA / CSR) ─────────────────────────────

    private function scrapeWithBrowsershot(string $url): array
    {
        $script = base_path('resources/puppeteer/scrape.cjs');

        if (!file_exists($script)) return [];

        try {
            $process = new Process(
                ['node', $script, $url, '45000'],
                null,
                null,
                null,
                60  // timeout process en secondes
            );
            $process->run();
            $html = $process->getOutput();
        } catch (\Throwable) {
            return [];
        }

        if (empty($html) || strlen($html) < 500) return [];

        // Même pipeline d'extraction sur le HTML rendu
        $products = $this->extractFromJsonLd($html, $url);

        if (empty($products)) {
            $products = $this->extractFromOpenGraph($html, $url);
        }

        if (empty($products)) {
            $products = $this->extractFromEmbeddedJs($html, $url);
        }

        // Extraction DOM : produits rendus sous forme de grille HTML
        if (empty($products)) {
            $products = $this->extractFromRenderedDom($html, $url);
        }

        if (empty($products)) {
            $products = $this->extractFromHtmlPatterns($html, $url);
        }

        // Crawl des liens produit si page liste
        if (count($products) <= 1) {
            $productUrls = $this->extractProductLinks($html, $url);
            if (count($productUrls) >= 2) {
                $products = $this->crawlProductPages($productUrls);
            }
        }

        return $products;
    }

    /**
     * Extraction DOM pour les pages SPA rendues :
     * cherche des blocs <a href="..."> contenant une image ET un texte de prix proches.
     */
    private function extractFromRenderedDom(string $html, string $pageUrl): array
    {
        $scheme = parse_url($pageUrl, PHP_URL_SCHEME) ?? 'https';
        $host   = parse_url($pageUrl, PHP_URL_HOST) ?? '';
        $base   = $scheme . '://' . $host;

        // Extraire tous les liens qui semblent être des pages produit
        preg_match_all(
            '/<a\s[^>]*href=["\']([^"\'#][^"\']*)["\'][^>]*>(.*?)<\/a>/is',
            $html,
            $anchors,
            PREG_SET_ORDER
        );

        $products = [];
        $seen     = [];

        foreach ($anchors as $anchor) {
            $href    = trim($anchor[1]);
            $content = $anchor[2];

            // Normaliser l'URL
            if (str_starts_with($href, '//')) $href = $scheme . ':' . $href;
            if (str_starts_with($href, '/'))  $href = $base . $href;
            if (!str_starts_with($href, 'http')) continue;

            // Même domaine
            if ((parse_url($href, PHP_URL_HOST) ?? '') !== $host) continue;

            $path  = parse_url($href, PHP_URL_PATH) ?? '/';
            $clean = $base . $path;

            if (isset($seen[$clean])) continue;
            if ($this->productLinkScore($path) === 0) continue;

            // L'ancre doit contenir une image
            if (!preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $imgM)) continue;
            $imgSrc = $imgM[1];
            if (!str_starts_with($imgSrc, 'http')) continue;

            // Chercher un nom : alt de l'image ou premier texte
            $name = null;
            if (preg_match('/alt=["\']([^"\']{4,})["\']/', $content, $altM)) {
                $name = html_entity_decode(trim($altM[1]));
            }
            if (!$name) {
                $text = trim(strip_tags($content));
                if (mb_strlen($text) >= 4) $name = mb_substr($text, 0, 150);
            }
            if (!$name) continue;

            // Chercher un prix dans ou juste après l'ancre (200 chars)
            $pos       = strpos($html, $anchor[0]);
            $nearby    = $pos !== false ? substr($html, $pos, strlen($anchor[0]) + 200) : $content;
            $price     = null;
            if (preg_match('/(\d[\d\s.,]*\d)\s*(?:€|EUR|GNF|FCFA|USD|\$)/i', $nearby, $pm)) {
                $v = (float) str_replace([' ', ','], ['', '.'], preg_replace('/[^\d.,]/', '', $pm[1]));
                if ($v > 0) $price = $v;
            }

            $seen[$clean] = true;
            $products[] = [
                'id'          => md5($name . $clean),
                'name'        => $name,
                'price'       => $price,
                'image_url'   => $imgSrc,
                'source_url'  => $clean,
                'description' => null,
            ];

            if (count($products) >= $this->maxProducts) break;
        }

        return $products;
    }

    // ─── Extraction directe ────────────────────────────────────────────

    private function extractFromJsonLd(string $html, string $pageUrl): array
    {
        $products = [];

        preg_match_all(
            '/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is',
            $html, $matches
        );

        foreach ($matches[1] as $raw) {
            try {
                $data = json_decode(trim($raw), true);
                if (!$data) continue;

                $nodes = isset($data[0]) ? $data : [$data];

                foreach ($nodes as $node) {
                    $items = isset($node['@graph']) ? $node['@graph'] : [$node];

                    foreach ($items as $item) {
                        $type = $this->schemaType($item);

                        if ($type === 'Product') {
                            $p = $this->normalizeSchemaProduct($item, $pageUrl);
                            if ($p) $products[] = $p;
                        }

                        if ($type === 'ItemList' && isset($item['itemListElement'])) {
                            foreach ($item['itemListElement'] as $el) {
                                $p = $this->normalizeSchemaProduct($el['item'] ?? $el, $pageUrl);
                                if ($p) $products[] = $p;
                            }
                        }
                    }
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $products;
    }

    private function extractFromOpenGraph(string $html, string $pageUrl): array
    {
        $og = [];
        preg_match_all(
            '/<meta\s+(?:property|name)=["\'](?:og:)?([^"\']+)["\']\s+content=["\']([^"\']*)["\'][^>]*>/i',
            $html, $m
        );
        for ($i = 0; $i < count($m[1]); $i++) {
            $og[$m[1][$i]] = html_entity_decode($m[2][$i]);
        }

        $name = $og['title'] ?? null;
        if (!$name) return [];

        $price = null;
        foreach (['price:amount', 'product:price:amount'] as $k) {
            if (!empty($og[$k])) {
                $price = (float) str_replace(',', '.', preg_replace('/[^\d.,]/', '', $og[$k]));
                break;
            }
        }

        return [[
            'id'          => md5($name . $pageUrl),
            'name'        => trim($name),
            'price'       => $price,
            'image_url'   => $og['image'] ?? null,
            'source_url'  => $og['url'] ?? $pageUrl,
            'description' => $og['description'] ?? null,
        ]];
    }

    private function extractFromEmbeddedJs(string $html, string $pageUrl): array
    {
        $products = [];

        // Collect all script tag contents
        preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $html, $scriptMatches);
        $scripts = $scriptMatches[1] ?? [];

        // Also grab window.X = {...} and window.__X__ = {...} assignments from raw HTML
        // (some sites inline them outside script tags or in very large blocks)
        $candidates = [];

        foreach ($scripts as $script) {
            // Match: window.X = { ... } or window.__X__ = { ... }
            preg_match_all(
                '/window(?:\.__?[\w$]+__?|\[[\'"]([\w$]+)[\'"]\])\s*=\s*(\{.*)/is',
                $script,
                $winMatches
            );
            foreach ($winMatches[2] as $jsonStr) {
                $candidates[] = $this->extractFirstJson($jsonStr);
            }

            // Also: var X = {...} / const X = {...} / let X = {...}
            preg_match_all(
                '/(?:var|const|let)\s+\w+\s*=\s*(\{[^;]{200,})/is',
                $script,
                $varMatches
            );
            foreach ($varMatches[1] as $jsonStr) {
                $candidates[] = $this->extractFirstJson($jsonStr);
            }

            // Large standalone JSON objects in script (e.g. Next.js __NEXT_DATA__)
            preg_match_all('/(\{[^<]{500,}\})/s', $script, $blobMatches);
            foreach ($blobMatches[1] as $blob) {
                $candidates[] = $blob;
            }
        }

        foreach ($candidates as $raw) {
            if (!$raw) continue;
            try {
                $data = json_decode($raw, true);
                if (!is_array($data)) continue;

                $found = $this->findProductsInJson($data, $pageUrl);
                foreach ($found as $p) {
                    $products[] = $p;
                    if (count($products) >= $this->maxProducts) return $products;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $products;
    }

    /**
     * Truncate a JSON string to its first complete top-level object.
     * Handles trailing JS (semicolons, function calls) gracefully.
     */
    private function extractFirstJson(string $raw): ?string
    {
        $raw   = trim($raw);
        $depth = 0;
        $len   = strlen($raw);

        for ($i = 0; $i < $len; $i++) {
            $c = $raw[$i];
            if ($c === '{' || $c === '[') $depth++;
            elseif ($c === '}' || $c === ']') {
                $depth--;
                if ($depth === 0) return substr($raw, 0, $i + 1);
            }
        }

        return null;
    }

    /**
     * Recursively walks a decoded JSON structure looking for arrays
     * whose items look like products (name + price or image).
     * Returns at most $this->maxProducts normalised product arrays.
     */
    private function findProductsInJson(array $data, string $pageUrl, int $depth = 0): array
    {
        if ($depth > 8) return [];

        $products = [];

        // Is $data itself a list of products?
        if (isset($data[0]) && is_array($data[0])) {
            $sample = $data[0];
            if ($this->looksLikeProduct($sample)) {
                foreach ($data as $item) {
                    if (!is_array($item)) continue;
                    $p = $this->extractProductFromJson($item, $pageUrl);
                    if ($p) $products[] = $p;
                    if (count($products) >= $this->maxProducts) return $products;
                }
                if ($products) return $products;
            }
        }

        // Walk keyed arrays looking for product-list candidates
        foreach ($data as $key => $value) {
            if (!is_array($value)) continue;

            // Keys that typically hold product lists
            $keyLower = strtolower((string) $key);
            $isProductKey = preg_match(
                '/product|goods|item|article|catalog|listing|result|data|list/i',
                $keyLower
            );

            // Numeric array of objects
            if (isset($value[0]) && is_array($value[0])) {
                if ($isProductKey || $this->looksLikeProduct($value[0])) {
                    $found = [];
                    foreach ($value as $item) {
                        if (!is_array($item)) continue;
                        $p = $this->extractProductFromJson($item, $pageUrl);
                        if ($p) $found[] = $p;
                        if (count($found) >= $this->maxProducts) break;
                    }
                    if (count($found) >= 2) {
                        foreach ($found as $p) $products[] = $p;
                        if (count($products) >= $this->maxProducts) return $products;
                        continue; // don't recurse into a list we already consumed
                    }
                }
            }

            // Recurse
            $sub = $this->findProductsInJson($value, $pageUrl, $depth + 1);
            foreach ($sub as $p) {
                $products[] = $p;
                if (count($products) >= $this->maxProducts) return $products;
            }
        }

        return $products;
    }

    /**
     * Heuristic: does this associative array look like a single product?
     */
    private function looksLikeProduct(array $item): bool
    {
        $keys = array_map('strtolower', array_keys($item));

        $hasName = (bool) array_intersect($keys, [
            'name', 'title', 'goods_name', 'product_name', 'item_name',
            'display_name', 'nom', 'productname', 'good_name',
        ]);

        $hasPrice = (bool) array_intersect($keys, [
            'price', 'prix', 'amount', 'cost', 'original_price',
            'sale_price', 'price_info', 'retail_price', 'display_price',
        ]);

        $hasImage = (bool) array_intersect($keys, [
            'image', 'image_url', 'img', 'thumbnail', 'picture',
            'goods_image', 'product_image', 'main_image', 'cover_image',
            'image_urls', 'images',
        ]);

        return $hasName && ($hasPrice || $hasImage);
    }

    /**
     * Normalise a raw JSON product object into our standard array shape.
     * Handles the various field-naming conventions across sites.
     */
    private function extractProductFromJson(array $item, string $pageUrl): ?array
    {
        // ── Name ──────────────────────────────────────────────────────
        $name = null;
        foreach (['name','title','goods_name','product_name','item_name','display_name','good_name','productname','nom'] as $k) {
            if (!empty($item[$k]) && is_string($item[$k])) { $name = $item[$k]; break; }
        }
        if (!$name) return null;

        // ── Price ─────────────────────────────────────────────────────
        $price = null;
        foreach (['price','sale_price','original_price','retail_price','display_price','amount','prix','cost'] as $k) {
            if (!isset($item[$k])) continue;
            $raw = $item[$k];
            // price may be nested: {price_info: {price: "12.99"}}
            if (is_array($raw)) {
                foreach (['price','amount','value','display_price'] as $sub) {
                    if (!empty($raw[$sub])) { $raw = $raw[$sub]; break; }
                }
            }
            if (is_scalar($raw)) {
                $v = (float) str_replace([',', ' '], ['.', ''], preg_replace('/[^\d.,]/', '', (string) $raw));
                if ($v > 0) { $price = $v; break; }
            }
        }

        // ── Image ─────────────────────────────────────────────────────
        $image = null;
        foreach (['image','image_url','thumbnail','goods_image','main_image','cover_image','picture','img'] as $k) {
            if (empty($item[$k])) continue;
            $raw = $item[$k];
            if (is_string($raw) && str_starts_with($raw, 'http')) { $image = $raw; break; }
            if (is_array($raw)) {
                $first = $raw[0] ?? null;
                if (is_string($first) && str_starts_with($first, 'http')) { $image = $first; break; }
                if (is_array($first) && !empty($first['url'])) { $image = $first['url']; break; }
            }
        }

        // ── URL ───────────────────────────────────────────────────────
        $url = null;
        foreach (['url','link','product_url','goods_url','href','canonical'] as $k) {
            if (!empty($item[$k]) && is_string($item[$k])) {
                $u = $item[$k];
                if (!str_starts_with($u, 'http')) {
                    $scheme = parse_url($pageUrl, PHP_URL_SCHEME) ?? 'https';
                    $host   = parse_url($pageUrl, PHP_URL_HOST) ?? '';
                    $u = $scheme . '://' . $host . '/' . ltrim($u, '/');
                }
                $url = $u;
                break;
            }
        }

        return [
            'id'          => md5($name . ($url ?? $pageUrl)),
            'name'        => trim($name),
            'price'       => $price,
            'image_url'   => $image,
            'source_url'  => $url ?? $pageUrl,
            'description' => isset($item['description']) && is_string($item['description'])
                ? mb_substr(strip_tags($item['description']), 0, 500)
                : null,
        ];
    }

    private function extractFromHtmlPatterns(string $html, string $pageUrl): array
    {
        $name = null;

        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            $name = preg_replace('/\s*[-|–]\s*[^-|–]+$/', '', trim(strip_tags($m[1])));
            $name = trim($name);
        }
        if (!$name && preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $m)) {
            $name = trim(strip_tags($m[1]));
        }
        if (!$name) return [];

        $image = null;
        if (preg_match('/<meta[^>]*property=["\']og:image["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $m)) {
            $image = $m[1];
        }

        $price = null;
        if (preg_match('/(\d[\d\s.,]*\d)\s*(?:GNF|€|EUR|USD|\$|FCFA)/i', $html, $m)) {
            $v = str_replace([' ', ','], ['', '.'], preg_replace('/[^\d., ]/', '', $m[1]));
            $price = (float) $v ?: null;
        }

        return [[
            'id'          => md5($name . $pageUrl),
            'name'        => $name,
            'price'       => $price,
            'image_url'   => $image,
            'source_url'  => $pageUrl,
            'description' => null,
        ]];
    }

    // ─── Crawl de page de résultats ────────────────────────────────────

    /**
     * Extrait les liens produit d'une page HTML par score de "ressemblance produit".
     * Ne se base sur aucun site spécifique.
     */
    private function extractProductLinks(string $html, string $pageUrl): array
    {
        $scheme = parse_url($pageUrl, PHP_URL_SCHEME) ?? 'https';
        $host   = parse_url($pageUrl, PHP_URL_HOST) ?? '';
        $base   = $scheme . '://' . $host;

        preg_match_all('/<a[^>]+href=["\']([^"\'#][^"\']*)["\'][^>]*>/i', $html, $m);

        $scored = [];

        foreach (array_unique($m[1]) as $href) {
            $href = trim($href);
            if (str_starts_with($href, '//')) $href = $scheme . ':' . $href;
            if (str_starts_with($href, '/'))  $href = $base . $href;
            if (!str_starts_with($href, 'http')) continue;

            // Même domaine uniquement
            $linkHost = parse_url($href, PHP_URL_HOST) ?? '';
            if ($linkHost !== $host) continue;

            // URL normalisée (sans query/fragment)
            $path  = parse_url($href, PHP_URL_PATH) ?? '/';
            $clean = $base . $path;

            $score = $this->productLinkScore($path);
            if ($score > 0) {
                $scored[$clean] = $score;
            }
        }

        // Trier par score décroissant
        arsort($scored);

        return array_slice(array_keys($scored), 0, $this->maxProducts);
    }

    /**
     * Score de 1 à N indiquant à quel point une URL ressemble à une page produit.
     * Retourne 0 si l'URL est clairement une page de navigation.
     */
    private function productLinkScore(string $path): int
    {
        if (strlen($path) <= 1) return 0;

        $segments = array_values(array_filter(explode('/', strtolower($path))));
        if (empty($segments)) return 0;

        // Exclusion immédiate si n'importe quel segment est un mot de navigation
        foreach ($segments as $seg) {
            if ($this->isNavSegment($seg)) return 0;
        }

        // Exclusion : extensions statiques
        $last = end($segments);
        if (preg_match('/\.(pdf|xml|json|css|js|png|jpg|jpeg|gif|webp|svg|ico|woff|ttf)$/i', $last)) {
            return 0;
        }

        // Exclusion : trop profond (rarement un produit)
        if (count($segments) > 4) return 0;

        $score = 1; // base

        // Longueur du dernier segment : les slugs produits sont descriptifs
        $len = strlen($last);
        if ($len >= 20) $score += 4;
        elseif ($len >= 12) $score += 3;
        elseif ($len >= 7)  $score += 2;
        elseif ($len >= 4)  $score += 1;
        else return 0; // trop court = probablement navigation

        // Nombre de tirets : "iphone-15-pro-max" → très probablement un produit
        $hyphens = substr_count($last, '-');
        if ($hyphens >= 3) $score += 4;
        elseif ($hyphens >= 2) $score += 3;
        elseif ($hyphens >= 1) $score += 2;

        // Présence d'un ID numérique : référence produit
        if (preg_match('/\d{3,}/', $last)) $score += 2;

        return $score;
    }

    /**
     * Retourne true si le segment est un mot-clé de navigation connu.
     * Couvre le français, l'anglais et les termes e-commerce courants.
     */
    private function isNavSegment(string $seg): bool
    {
        static $nav = [
            // Panier / commande
            'cart','panier','basket','sac','bag','checkout','commande','order','orders','commander',
            'paiement','payment','pay','payer','facture','invoice','billing',
            // Compte / auth
            'login','signin','sign-in','connexion','se-connecter','logout','signout','deconnexion',
            'register','signup','sign-up','inscription','create-account','creer-compte',
            'account','accounts','compte','mon-compte','my-account','dashboard','espace-client',
            'profile','profil','settings','parametres','preferences',
            'password','mot-de-passe','reset-password','forgot-password',
            // Livraison / retour
            'livraison','delivery','shipping','expedition','retour','return','returns','refund',
            'remboursement','suivi','tracking','track','colis',
            // Wishlist / comparateur
            'wishlist','liste-de-souhaits','favoris','favorites','compare','comparateur',
            // Pages info
            'about','about-us','a-propos','qui-sommes-nous','qui-nous-sommes',
            'contact','contactez-nous','contact-us',
            'faq','help','aide','support','assistance','service-client','customer-service',
            // Légal
            'terms','cgu','cgv','conditions','mentions','mentions-legales','legal',
            'privacy','politique','confidentialite','rgpd','gdpr','cookies',
            // Blog / contenu
            'blog','news','actualites','presse','press','article','articles',
            'magazine','guide','guides','tutoriel',
            // Navigation site
            'sitemap','plan-du-site','home','accueil','index',
            'search','recherche','results','resultats',
            // Technique
            'static','assets','cdn','images','img','css','js','fonts','media',
            'api','ajax','webhook',
            // Réseaux sociaux / partage
            'share','partager','social',
            // Promotions (pages liste, pas produit)
            'wishlist','newsletter','abonnement',
        ];

        return in_array($seg, $nav, true);
    }

    /**
     * Crawl en parallèle les pages produit (par batch de 5 pour ne pas surcharger).
     */
    private function crawlProductPages(array $urls): array
    {
        $products = [];
        $headers  = [
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'fr-FR,fr;q=0.9,en-US;q=0.8',
            'DNT'             => '1',
        ];

        // Traiter par batch de 5 requêtes parallèles
        foreach (array_chunk($urls, 5) as $batch) {
            try {
                $responses = \Illuminate\Support\Facades\Http::pool(function ($pool) use ($batch, $headers) {
                    foreach ($batch as $i => $url) {
                        $pool->as($i)
                            ->withHeaders($headers)
                            ->timeout($this->timeout)
                            ->withOptions(['verify' => false])
                            ->get($url);
                    }
                });
            } catch (\Throwable) {
                continue;
            }

            foreach ($batch as $i => $url) {
                $response = $responses[$i] ?? null;
                if (!$response || !$response->successful()) continue;

                $html  = $response->body();
                $found = $this->extractFromJsonLd($html, $url);

                if (empty($found)) $found = $this->extractFromOpenGraph($html, $url);
                if (empty($found)) $found = $this->extractFromEmbeddedJs($html, $url);
                if (empty($found)) $found = $this->extractFromHtmlPatterns($html, $url);

                foreach ($found as $p) {
                    $products[] = $p;
                }
            }

            // Pause courte entre les batchs
            usleep(500_000);
        }

        return $products;
    }

    private function schemaType(array $node): ?string
    {
        if (!isset($node['@type'])) return null;
        $t = $node['@type'];
        return is_array($t) ? ($t[0] ?? null) : $t;
    }

    private function normalizeSchemaProduct(array $data, string $pageUrl): ?array
    {
        $name = $data['name'] ?? null;
        if (!$name) return null;

        // Prix
        $price = null;
        if (isset($data['offers'])) {
            $o = $data['offers'];
            $raw = is_array($o) ? ($o['price'] ?? $o[0]['price'] ?? null) : null;
            if ($raw !== null) {
                $v = str_replace(',', '.', preg_replace('/[^\d.,]/', '', (string) $raw));
                $price = $v !== '' ? (float) $v : null;
            }
        }

        // Image
        $image = null;
        if (isset($data['image'])) {
            $img = $data['image'];
            $image = is_string($img) ? $img : ($img['url'] ?? (is_string($img[0] ?? null) ? $img[0] : null));
        }

        $url  = $data['url'] ?? $data['@id'] ?? $pageUrl;
        $desc = isset($data['description']) ? mb_substr(strip_tags($data['description']), 0, 500) : null;

        return [
            'id'          => md5($name . $url),
            'name'        => trim((string) $name),
            'price'       => $price,
            'image_url'   => $image,
            'source_url'  => (string) $url,
            'description' => $desc,
        ];
    }

    private function deduplicate(array $products): array
    {
        $seen = [];
        $out  = [];
        foreach ($products as $p) {
            if (!isset($seen[$p['id']])) {
                $seen[$p['id']] = true;
                $out[] = $p;
            }
        }
        return array_values($out);
    }
}
