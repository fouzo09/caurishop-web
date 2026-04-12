<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    public const TYPE_SIMPLE = 'simple';
    public const TYPE_VARIABLE = 'variable';

    protected $fillable = [
        'type',
        'name',
        'slug',
        'description',
        'sku',
        'price',
        'stock_quantity',
        'low_stock_threshold',
        'is_published',
        'is_active',
        'credit_enabled',
        'credit_duration_months',
        'credit_installments_count',
    ];

    protected $casts = [
        'price'                    => 'decimal:2',
        'stock_quantity'           => 'integer',
        'low_stock_threshold'      => 'integer',
        'is_published'             => 'boolean',
        'is_active'                => 'boolean',
        'credit_enabled'           => 'boolean',
        'credit_duration_months'   => 'integer',
        'credit_installments_count'=> 'integer',
    ];

    protected $appends = ['display_price', 'stock_status', 'display_monthly_payment'];

    // Relations
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true)->where('is_active', true);
    }

    public function scopeSimple($query)
    {
        return $query->where('type', self::TYPE_SIMPLE);
    }

    public function scopeVariable($query)
    {
        return $query->where('type', self::TYPE_VARIABLE);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($q2) {
                $q2->where('type', self::TYPE_SIMPLE)
                   ->where('stock_quantity', '>', 0);
            })->orWhere('type', self::TYPE_VARIABLE);
        });
    }

    // Type checks
    public function isSimple(): bool
    {
        return $this->type === self::TYPE_SIMPLE;
    }

    public function isVariable(): bool
    {
        return $this->type === self::TYPE_VARIABLE;
    }

    // Stock management
    public function hasStock(): bool
    {
        if ($this->isSimple()) {
            return $this->stock_quantity > 0;
        }

        return $this->variants()->where('is_active', true)->sum('stock_quantity') > 0;
    }

    public function totalStock(): int
    {
        if ($this->isSimple()) {
            return (int) $this->stock_quantity;
        }

        return (int) $this->variants()->where('is_active', true)->sum('stock_quantity');
    }

    public function isLowStock(): bool
    {
        if ($this->isVariable()) {
            return false;
        }

        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        if ($this->isVariable()) {
            return !$this->hasStock();
        }

        return $this->stock_quantity <= 0;
    }

    // Price & Credit
    public function displayPrice(): string
    {
        if ($this->isVariable()) {
            return 'Varie';
        }

        return number_format($this->price, 0, ',', ' ') . ' GNF';
    }

    public function monthlyPayment(): ?float
    {
        if (!$this->credit_enabled || !$this->credit_installments_count || $this->isVariable()) {
            return null;
        }

        return $this->price / $this->credit_installments_count;
    }

    public function displayMonthlyPayment(): ?string
    {
        $monthly = $this->monthlyPayment();
        return $monthly ? number_format($monthly, 0, ',', ' ') . ' GNF' : null;
    }

    // Accessors
    public function getDisplayPriceAttribute(): string
    {
        return $this->displayPrice();
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->isVariable()) {
            return $this->hasStock() ? 'disponible' : 'rupture';
        }

        if ($this->isOutOfStock()) return 'rupture';
        if ($this->isLowStock())   return 'faible';
        return 'disponible';
    }

    public function getDisplayMonthlyPaymentAttribute(): ?string
    {
        return $this->displayMonthlyPayment();
    }

    public function coverUrl(): ?string
    {
        $primary = $this->images->firstWhere('is_primary', true)
            ?? $this->images->first();

        return $primary?->url;
    }
}
