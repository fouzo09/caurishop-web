# Mission — Intégration du flow e-commerce classique (Phase 1)

## 1. Contexte du projet

Ce projet est une plateforme e-commerce Laravel (monolithe, pattern Controllers / Services / Repository, PostgreSQL) initialement conçue pour des **professionnels** : les clients devaient se connecter à une interface d'administration pour consulter les produits et passer des commandes, payables au comptant ou à crédit (remboursement échelonné).

**Problème constaté lors des tests utilisateurs** : ce parcours dérouté les utilisateurs, habitués à un e-commerce classique.

**Décision** : pivoter vers un flow e-commerce traditionnel, ouvert à tous (navigation publique du catalogue, inscription libre, commande, paiement), tout en conservant le système existant intact pour l'instant.

## 2. Règle d'or : NE PAS CASSER L'EXISTANT

Avant d'écrire la moindre ligne de code :

1. **Explore d'abord le projet existant** : routes, modèles, migrations, services, contrôleurs. Fais-moi un résumé de ce que tu as compris (entités principales, flow de commande actuel, système d'authentification) et attends ma validation avant de commencer.
2. L'interface admin existante et son flow de commande **doivent continuer à fonctionner à l'identique**. Tout le nouveau code vit à côté, pas à la place.
3. **Migrations additives uniquement** : de nouvelles tables ou de nouvelles colonnes nullable. Interdiction de renommer, supprimer ou modifier des colonnes/tables existantes.
4. **Réutilise** les modèles, services et repositories existants (produits, commandes, clients) partout où c'est possible plutôt que de dupliquer la logique. Si un service existant ne convient pas, étends-le ou crée une variante, ne le modifie pas de façon destructive.
5. Travaille sur une branche dédiée (ex. `feature/storefront`), avec des commits petits et fréquents, un par étape fonctionnelle.

## 3. Objectif de la Phase 1 : le flow e-commerce classique

Développer la partie publique ("storefront") avec ce parcours :

1. **Page d'accueil** — bannière promo, catégories, meilleures ventes, nouveautés
2. **Inscription / Connexion** — ouverte à tout le monde (e-mail ou téléphone + mot de passe)
3. **Liste des produits** — avec filtres (catégorie, prix), tri et pagination
4. **Détail produit** — galerie, variantes (ex. taille, couleur), quantité, ajout au panier
5. **Panier** — modification des quantités, suppression, code promo, récapitulatif
6. **Validation de commande (checkout)** — adresse de livraison, mode de livraison, moyen de paiement
7. **Paiement** — au comptant uniquement pour cette phase : Orange Money, MTN MoMo, carte, virement, à la livraison. Implémente une couche d'abstraction (interface `PaymentProvider` ou équivalent) avec un provider "simulé" pour commencer ; les intégrations réelles viendront après.
8. **Confirmation de commande** — récapitulatif + statut de suivi
9. **Espace client minimal** — mes commandes, mon profil, mes adresses

Un visiteur non connecté peut naviguer et remplir son panier ; la connexion/inscription n'est exigée qu'au moment du checkout.

## 4. Template fourni : dossier `tpl`

Le design complet est fourni dans le dossier `tpl` (HTML + CSS + Bootstrap 5) :

| Fichier template | Vue Laravel cible (suggestion) |
|---|---|
| `index.html` | `resources/views/storefront/home.blade.php` |
| `boutique.html` | `storefront/products/index.blade.php` |
| `produit.html` | `storefront/products/show.blade.php` |
| `panier.html` | `storefront/cart.blade.php` |
| `checkout.html` | `storefront/checkout.blade.php` |
| `confirmation.html` | `storefront/orders/confirmation.blade.php` |
| `compte.html` | `auth/login.blade.php` + `auth/register.blade.php` |
| `contact.html` | `storefront/contact.blade.php` |

Consignes d'intégration :

- Le header et le footer sont identiques sur toutes les pages du template : extrais-les en **layout + partials Blade** (`layouts/storefront.blade.php`, `partials/header.blade.php`, `partials/footer.blade.php`).
- Copie `assets/css/style.css` et `assets/js/main.js` dans `public/` (ou dans le pipeline Vite si le projet en utilise un).
- Remplace les données statiques du template (produits, prix, catégories, totaux) par les données dynamiques venant de la base. Les prix sont en **GNF** (francs guinéens), formatés à la française : `1 250 000 GNF`.
- Les images produits du template sont des URLs Unsplash provisoires : branche-les sur le système d'images des produits existant (avec une image par défaut si un produit n'en a pas).
- Respecte fidèlement le design du template (couleurs, espacements, composants). N'invente pas un autre style.

## 5. Points d'architecture à anticiper (sans les implémenter)

La **Phase 2** réintroduira le concept d'origine : les clients inscrits via un **lien d'invitation d'une entreprise** pourront payer **à crédit** (remboursement échelonné, "molo molo"). Pour ne pas se bloquer :

- Prévois sur le modèle client/utilisateur la possibilité d'un rattachement optionnel à une entreprise (colonne nullable ou table de liaison, sans logique métier pour l'instant).
- Conçois le checkout pour que la liste des moyens de paiement soit **extensible** (le paiement à crédit deviendra un moyen de paiement conditionnel, visible seulement pour les clients éligibles).
- Le modèle de commande doit pouvoir accueillir plus tard un échéancier de paiement (ne crée pas la table maintenant, mais ne verrouille rien qui l'empêcherait).

**N'implémente rien de la Phase 2 sans que je le demande explicitement.**

## 6. Ordre de travail suggéré

1. Exploration de l'existant + résumé + plan détaillé → **validation par moi**
2. Layout Blade + header/footer + page d'accueil (statique branchée sur les vraies catégories/produits)
3. Liste des produits + détail produit
4. Authentification publique (inscription/connexion) sans toucher à l'auth admin
5. Panier (session pour les invités, persisté en base pour les connectés)
6. Checkout + création de commande (en réutilisant le service de commande existant si possible)
7. Couche paiement (interface + provider simulé) + page de confirmation
8. Espace client minimal
9. Tests (feature tests sur le parcours complet) + vérification que l'admin existant fonctionne toujours

À chaque étape : montre-moi ce que tu prévois de faire avant les changements structurants (nouvelles tables, modification de services partagés).

## 7. Critères de réussite

- Le parcours complet visiteur → inscription → commande → paiement simulé → confirmation fonctionne de bout en bout.
- L'interface admin et le flow de commande existants fonctionnent exactement comme avant (aucune régression).
- Le rendu est fidèle au template `caurishop/`.
- Les tests passent, y compris les tests existants du projet.
