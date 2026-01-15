<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'attributes',
        'price',
        'is_active',
        'credit_enabled',
        'credit_duration_months',
        'credit_installments_count',
    ];

    protected $casts = [
        'attributes' => 'array', // jsonb
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'credit_enabled' => 'boolean',
        'credit_duration_months' => 'integer',
        'credit_installments_count' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Crédit effectif: si override défini sur variante -> sinon produit.
     */
    public function effectiveCreditEnabled(): bool
    {
        if (!is_null($this->credit_enabled)) {
            return (bool) $this->credit_enabled;
        }

        return (bool) $this->product?->credit_enabled;
    }

    public function effectiveCreditDurationMonths(): ?int
    {
        return $this->credit_duration_months ?? $this->product?->credit_duration_months;
    }

    public function effectiveCreditInstallmentsCount(): ?int
    {
        return $this->credit_installments_count ?? $this->product?->credit_installments_count;
    }
}
