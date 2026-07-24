# Plan d'action — Phase 1 : parcours e-commerce public (`shop`)

> Référence : `consignes.md`. Règle d'or : **ne pas casser l'existant** (`/admin`, `/company`,
> `/portal`, `AuthController`, `OrderService`, Djomy). Migrations **additives uniquement**.
> Branche dédiée : `feature/shop-public`. Commits petits et fréquents (un par étape).

## Décisions actées (validées le 2026-07-25)

- **Boutique à la racine `/`**, nommée **`shop`** dans le code : controllers
  `App\Http\Controllers\Shop\*`, vues `resources/views/shop/*`, routes `shop.*`.
  (Ne pas employer le mot « storefront ».)
- **Clients publics** = `Customer` type `individual`, `company_id = null`, rôle Spatie
  additif **`customer`** (aucune permission admin).
- **Catégories** : n'existent pas → **nouvelle table `categories`** + `products.category_id`
  nullable (+ seeder de catégories de départ).
- **Landing B2B supprimée** de `/` (remplacée par l'accueil boutique). Route **`/demarrer`
  conservée** (inscription entreprise, existant).
- **Paiement** : cash uniquement, derrière une abstraction `PaymentProvider` + `SimulatedProvider`.
  Djomy existant non modifié.
- **Panier** : session pour les invités, tables `carts`/`cart_items` pour les connectés.

## Conventions

- Prix en **GNF**, format français : `1 250 000 GNF`.
- Produits affichés côté public = `is_published = true` ET `is_active = true` (scope existant).
- Image produit par défaut si aucune (réutilise `ProductImage`).
- Réutiliser `OrderService::createOrder()` (`order_type = cash`) pour créer les commandes.

## Migrations additives à créer

1. `categories` (id, name, slug, is_active, timestamps) + `products.category_id` nullable + FK.
2. `orders` : colonnes **nullable** — `shipping_name`, `shipping_phone`, `shipping_address`,
   `shipping_city`, `delivery_method`, `payment_method`, `payment_status`.
3. `carts` (user_id) + `cart_items` (cart_id, product_id, variant_id, quantity).
4. Seeder additif : rôle Spatie `customer` + catégories de départ.

## Étapes d'exécution — ✅ TOUTES RÉALISÉES (branche `feature/shop-public`)

- [x] **E1. Fondations** — branche, migrations additives, modèle `Category`, relations,
  rôle `customer`, `User::homeRoute()` (+ branche additive), seeders.
- [x] **E2. Layout & accueil** — `shop/layouts/app` + partials header/footer depuis `tpl/`,
  assets dans `public/`, helper prix GNF, `HomeController` (catégories, meilleures ventes,
  nouveautés) → `shop/home`.
- [x] **E3. Catalogue** — `Shop\ProductController@index` (filtres catégorie/prix, tri,
  pagination) → `boutique.html` ; `@show` (galerie, variantes) → `produit.html`.
- [x] **E4. Auth publique** — `/inscription` + `/connexion` (email ou téléphone), crée
  `User`+`Customer`+rôle `customer`. `AuthController` admin intact.
- [x] **E5. Panier** — `Shop\CartController` (add/update/remove), session (invités) +
  `carts`/`cart_items` (connectés), fusion à la connexion → `panier.html`.
- [x] **E6. Checkout** — `Shop\CheckoutController` (adresse, livraison, paiement), création
  commande via `OrderService::createOrder()` → `checkout.html`.
- [x] **E7. Paiement** — interface `PaymentProvider` + `SimulatedProvider` + registre
  extensible ; page confirmation → `confirmation.html`.
- [x] **E8. Espace client** — `/mon-compte` : commandes, profil, adresses.
- [x] **E9. Contact** — `Shop\ContactController` → `contact.html`.
- [x] **E10. Tests & non-régression** — feature tests Pest du parcours complet ; vérifier que
  les tests existants et les flows admin/company/portal passent inchangés.

## Non-régression (à re-vérifier en fin de parcours)

- `/admin`, `/company`, `/portal` fonctionnent à l'identique.
- `AuthController` / `OrderService` / Djomy non modifiés de façon destructive.
- `php artisan test` vert (existants + nouveaux).
