<?php

namespace App\Services\Shop;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;

/**
 * Panier du parcours public.
 * - Invités : stocké en session (clé "cart").
 * - Connectés : persisté en base (tables carts / cart_items).
 *
 * Représentation interne d'une ligne : ['product_id' => int, 'variant_id' => ?int, 'quantity' => int].
 */
class CartService
{
    public const SESSION_KEY = 'cart';
    public const PROMO_KEY = 'cart_promo';

    /**
     * Codes promo disponibles (taux de remise). Extensible en Phase 2.
     *
     * @return array<string, float>
     */
    public function promoCodes(): array
    {
        return [
            'KARITE25' => 0.05, // -5%
            'BIENVENUE' => 0.10, // -10%
        ];
    }

    /**
     * Applique un code promo (retourne true si valide).
     */
    public function applyPromo(string $code): bool
    {
        $code = strtoupper(trim($code));
        if (! array_key_exists($code, $this->promoCodes())) {
            return false;
        }

        session([self::PROMO_KEY => $code]);
        return true;
    }

    public function clearPromo(): void
    {
        session()->forget(self::PROMO_KEY);
    }

    public function promoCode(): ?string
    {
        $code = session(self::PROMO_KEY);
        return $code && array_key_exists($code, $this->promoCodes()) ? $code : null;
    }

    /**
     * Récapitulatif complet avec remise éventuelle.
     *
     * @return array{items: array, subtotal: float, promo: ?string, discount: float, total: float, count: int}
     */
    public function summary(): array
    {
        $detailed = $this->detailed();
        $promo    = $this->promoCode();
        $rate     = $promo ? $this->promoCodes()[$promo] : 0.0;
        $discount = round($detailed['subtotal'] * $rate);

        return [
            'items'    => $detailed['items'],
            'subtotal' => $detailed['subtotal'],
            'promo'    => $promo,
            'discount' => $discount,
            'total'    => max(0, $detailed['subtotal'] - $discount),
            'count'    => $detailed['count'],
        ];
    }

    /**
     * Clé unique d'une ligne (produit + variante).
     */
    protected function lineKey(int $productId, ?int $variantId): string
    {
        return $productId . ':' . ($variantId ?? '0');
    }

    /**
     * Retourne les lignes brutes du panier : ['product_id','variant_id','quantity'].
     *
     * @return array<int, array{product_id:int, variant_id:?int, quantity:int}>
     */
    public function rawLines(): array
    {
        if (Auth::check()) {
            $cart = $this->userCart(false);
            if (! $cart) {
                return [];
            }

            return $cart->items->map(fn ($item) => [
                'product_id' => (int) $item->product_id,
                'variant_id' => $item->variant_id ? (int) $item->variant_id : null,
                'quantity'   => (int) $item->quantity,
            ])->values()->all();
        }

        return array_values(session(self::SESSION_KEY, []));
    }

    /**
     * Nombre total d'articles (somme des quantités).
     */
    public function count(): int
    {
        return array_sum(array_map(fn ($l) => $l['quantity'], $this->rawLines()));
    }

    /**
     * Ajoute (ou incrémente) une ligne au panier.
     */
    public function add(int $productId, ?int $variantId, int $quantity = 1): void
    {
        $quantity = max(1, $quantity);

        if (Auth::check()) {
            $cart = $this->userCart(true);
            $item = $cart->items()
                ->where('product_id', $productId)
                ->where('variant_id', $variantId)
                ->first();

            if ($item) {
                $item->increment('quantity', $quantity);
            } else {
                $cart->items()->create([
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'quantity'   => $quantity,
                ]);
            }

            return;
        }

        $cart = session(self::SESSION_KEY, []);
        $key  = $this->lineKey($productId, $variantId);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity'   => $quantity,
            ];
        }

        session([self::SESSION_KEY => $cart]);
    }

    /**
     * Met à jour la quantité d'une ligne (supprime si quantité <= 0).
     */
    public function update(int $productId, ?int $variantId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($productId, $variantId);
            return;
        }

        if (Auth::check()) {
            $cart = $this->userCart(true);
            $cart->items()
                ->where('product_id', $productId)
                ->where('variant_id', $variantId)
                ->update(['quantity' => $quantity]);

            return;
        }

        $cart = session(self::SESSION_KEY, []);
        $key  = $this->lineKey($productId, $variantId);
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = $quantity;
            session([self::SESSION_KEY => $cart]);
        }
    }

    /**
     * Supprime une ligne.
     */
    public function remove(int $productId, ?int $variantId): void
    {
        if (Auth::check()) {
            $cart = $this->userCart(true);
            $cart->items()
                ->where('product_id', $productId)
                ->where('variant_id', $variantId)
                ->delete();

            return;
        }

        $cart = session(self::SESSION_KEY, []);
        unset($cart[$this->lineKey($productId, $variantId)]);
        session([self::SESSION_KEY => $cart]);
    }

    /**
     * Vide le panier.
     */
    public function clear(): void
    {
        if (Auth::check()) {
            $cart = $this->userCart(false);
            $cart?->items()->delete();

            return;
        }

        session()->forget(self::SESSION_KEY);
    }

    /**
     * Détaille le panier avec produits/variantes chargés et totaux.
     *
     * @return array{items: array<int, array>, subtotal: float, count: int}
     */
    public function detailed(): array
    {
        $items    = [];
        $subtotal = 0.0;

        foreach ($this->rawLines() as $line) {
            $product = Product::with('images')->find($line['product_id']);
            if (! $product) {
                continue;
            }

            $variant   = $line['variant_id'] ? ProductVariant::find($line['variant_id']) : null;
            $unitPrice = (float) ($variant?->price ?? $product->price);
            $lineTotal = $unitPrice * $line['quantity'];
            $subtotal += $lineTotal;

            $items[] = [
                'product'    => $product,
                'variant'    => $variant,
                'quantity'   => $line['quantity'],
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        return [
            'items'    => $items,
            'subtotal' => $subtotal,
            'count'    => array_sum(array_map(fn ($l) => $l['quantity'], $this->rawLines())),
        ];
    }

    /**
     * Fusionne le panier session (invité) dans le panier base à la connexion.
     */
    public function mergeSessionIntoUser(): void
    {
        $sessionCart = session(self::SESSION_KEY, []);
        if (empty($sessionCart) || ! Auth::check()) {
            return;
        }

        foreach ($sessionCart as $line) {
            $this->add($line['product_id'], $line['variant_id'], $line['quantity']);
        }

        session()->forget(self::SESSION_KEY);
    }

    /**
     * Récupère (ou crée) le panier base de l'utilisateur connecté.
     */
    protected function userCart(bool $create): ?Cart
    {
        $userId = Auth::id();

        if ($create) {
            return Cart::with('items')->firstOrCreate(['user_id' => $userId]);
        }

        return Cart::with('items')->where('user_id', $userId)->first();
    }
}
