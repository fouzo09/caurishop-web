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
        'supplier_price',
        'stock_quantity',
        'is_active',
        'credit_enabled',
        'credit_duration_months',
        'credit_installments_count',
    ];

    protected $casts = [
        'attributes'               => 'array',
        'price'                    => 'decimal:2',
        'supplier_price'           => 'decimal:2',
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

    /**
     * Attributs de la variante décodés : ['couleur' => 'Argent', 'stockage' => '256 Go'].
     *
     * On passe par getAttribute() : lu depuis une autre classe du même arbre
     * Eloquent, `$variant->attributes` renverrait la propriété protégée du
     * modèle (toutes les colonnes) au lieu de la colonne JSON castée.
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        return array_map(
            fn ($value) => trim((string) $value),
            (array) $this->getAttribute('attributes'),
        );
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'variant_id')->orderBy('sort_order')->orderBy('id');
    }

    /** Visuel de la variante, null si aucune image ne lui est rattachée. */
    public function coverUrl(): ?string
    {
        return $this->images->first()?->url;
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
