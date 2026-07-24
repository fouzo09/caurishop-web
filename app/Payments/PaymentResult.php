<?php

namespace App\Payments;

/**
 * Résultat d'une tentative de paiement.
 */
class PaymentResult
{
    public const PAID    = 'paid';     // réglé immédiatement (mobile money, carte simulés)
    public const PENDING = 'pending';  // en attente (virement, à la livraison)
    public const FAILED  = 'failed';

    public function __construct(
        public readonly string $status,
        public readonly ?string $reference = null,
        public readonly ?string $message = null,
    ) {
    }

    public function isPaid(): bool
    {
        return $this->status === self::PAID;
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::FAILED;
    }
}
