<?php

namespace App\Payments;

use App\Models\Customer;

/**
 * Registre des moyens de paiement. Point d'extension unique : ajouter un provider
 * ici (ou le rendre conditionnel via isAvailableFor) sans toucher au checkout.
 */
class PaymentManager
{
    /** @var array<string, PaymentProvider> */
    private array $providers = [];

    public function __construct()
    {
        $this->register(new SimulatedProvider('orange_money', 'Orange Money', '🟠', requiresPhone: true, instant: true));
        $this->register(new SimulatedProvider('mtn_momo', 'MTN MoMo', '🟡', requiresPhone: true, instant: true));
        $this->register(new SimulatedProvider('card', 'Visa / Paycard', '💳', requiresPhone: false, instant: true));
        $this->register(new SimulatedProvider('bank_transfer', 'Virement bancaire', '🏦', requiresPhone: false, instant: false));
        $this->register(new SimulatedProvider('cash_on_delivery', 'À la livraison', '💵', requiresPhone: false, instant: false));
    }

    public function register(PaymentProvider $provider): void
    {
        $this->providers[$provider->key()] = $provider;
    }

    public function get(string $key): ?PaymentProvider
    {
        return $this->providers[$key] ?? null;
    }

    /**
     * Moyens disponibles pour un client (ou visiteur).
     *
     * @return array<string, PaymentProvider>
     */
    public function available(?Customer $customer = null): array
    {
        return array_filter(
            $this->providers,
            fn (PaymentProvider $p) => $p->isAvailableFor($customer),
        );
    }
}
