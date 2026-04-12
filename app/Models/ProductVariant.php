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
        'stock_quantity',
        'is_active',
        'credit_enabled',
        'credit_duration_months',
        'credit_installments_count',
    ];

    protected $casts = [
        'attributes'               => 'array',
        'price'                    => 'decimal:2',
        'stock_quantity'           => 'integer',
        'is_active'                => 'boolean',
        'credit_enabled'           => 'boolean',
        'credit_duration_months'   => 'integer',
        'credit_installments_count'=> 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Stock
    public function hasStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    // Prix
    public function displayPrice(): string
    {
        return number_format($this->price, 0, ',', ' ') . ' GNF';
    }

    // Crédit effectif : override variante, sinon héritage du produit parent
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

    public function monthlyPayment(): ?float
    {
        $installments = $this->effectiveCreditInstallmentsCount();

        if (!$this->effectiveCreditEnabled() || !$installments) {
            return null;
        }

        return $this->price / $installments;
    }

    public function displayMonthlyPayment(): ?string
    {
        $monthly = $this->monthlyPayment();
        return $monthly ? number_format($monthly, 0, ',', ' ') . ' GNF' : null;
    }
}
