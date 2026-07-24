<?php

namespace App\Payments;

use App\Models\Customer;
use App\Models\Order;

/**
 * Contrat d'un moyen de paiement du parcours public.
 *
 * La Phase 2 ajoutera un provider "crédit" (paiement échelonné) conditionnel,
 * visible seulement pour les clients éligibles via isAvailableFor().
 */
interface PaymentProvider
{
    /** Identifiant technique stable (ex: "orange_money"). */
    public function key(): string;

    /** Libellé affiché (ex: "Orange Money"). */
    public function label(): string;

    /** Icône (emoji) affichée dans le sélecteur. */
    public function icon(): string;

    /** Le moyen requiert-il un numéro Mobile Money ? */
    public function requiresPhone(): bool;

    /** Disponible pour ce client ? (null = visiteur non connecté) */
    public function isAvailableFor(?Customer $customer): bool;

    /** Traite le paiement de la commande et renvoie le résultat. */
    public function process(Order $order, array $data): PaymentResult;
}
