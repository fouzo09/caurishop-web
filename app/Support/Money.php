<?php

namespace App\Support;

class Money
{
    /**
     * Formate un montant en francs guinéens, style français : "1 250 000 GNF".
     */
    public static function gnf($amount): string
    {
        return number_format((float) $amount, 0, ',', ' ') . ' GNF';
    }
}
