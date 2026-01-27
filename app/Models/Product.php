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
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'credit_enabled' => 'boolean',
        'credit_duration_months' => 'integer',
        'credit_installments_count' => 'integer',
    ];

    protected $appends = ['display_price', 'stock_status'];

    // Relations
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
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
        return $query->where('stock_quantity', '>', 0);
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

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold && $this->stock_quantity > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    // Price & Credit
    public function displayPrice(): string
    {
        return number_format($this->price, 0, ',', ' ') . ' GNF';
    }

    public function monthlyPayment(): ?float
    {
        if (!$this->credit_enabled || !$this->credit_installments_count) {
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
        if ($this->isOutOfStock()) return 'rupture';
        if ($this->isLowStock()) return 'faible';
        return 'disponible';
    }
}
