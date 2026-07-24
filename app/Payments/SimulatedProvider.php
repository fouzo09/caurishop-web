<?php

namespace App\Payments;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Provider "simulé" de la Phase 1 : aucune intégration réelle.
 * - Les moyens instantanés (mobile money, carte) sont marqués PAID immédiatement.
 * - Les moyens différés (virement, à la livraison) sont marqués PENDING.
 *
 * Les intégrations réelles (Djomy pour Orange/MTN, etc.) remplaceront cette classe
 * moyen par moyen sans toucher au reste du parcours.
 */
class SimulatedProvider implements PaymentProvider
{
    public function __construct(
        private readonly string $key,
        private readonly string $label,
        private readonly string $icon,
        private readonly bool $requiresPhone = false,
        private readonly bool $instant = true,
    ) {
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function icon(): string
    {
        return $this->icon;
    }

    public function requiresPhone(): bool
    {
        return $this->requiresPhone;
    }

    public function isAvailableFor(?Customer $customer): bool
    {
        // Phase 1 : tous les moyens au comptant sont disponibles pour tout le monde.
        return true;
    }

    public function process(Order $order, array $data): PaymentResult
    {
        $reference = 'SIM-' . strtoupper(Str::random(10));

        return new PaymentResult(
            status: $this->instant ? PaymentResult::PAID : PaymentResult::PENDING,
            reference: $reference,
            message: $this->instant
                ? 'Paiement simulé accepté.'
                : 'Commande enregistrée, paiement à finaliser.',
        );
    }
}
