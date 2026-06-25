<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformMargin extends Model
{
    public const SCOPE_GLOBAL  = 'global';
    public const SCOPE_PRODUCT = 'product';

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED      = 'fixed';

    protected $fillable = [
        'scope',
        'reference_id',
        'type',
        'value',
        'label',
        'is_active',
    ];

    protected $casts = [
        'value'     => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->where('scope', self::SCOPE_GLOBAL);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('scope', self::SCOPE_PRODUCT)
                     ->where('reference_id', $productId);
    }

    public function displayValue(): string
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            return number_format($this->value, 2) . ' %';
        }

        return number_format($this->value, 0, ',', ' ') . ' GNF';
    }

    public function apply(float $supplierPrice): float
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            return round($supplierPrice * (1 + (float) $this->value / 100), 2);
        }

        return round($supplierPrice + (float) $this->value, 2);
    }
}
