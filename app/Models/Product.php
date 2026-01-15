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
        'description',
        'sku',
        'price',
        'is_published',
        'is_active',
        'credit_enabled',
        'credit_duration_months',
        'credit_installments_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'credit_enabled' => 'boolean',
        'credit_duration_months' => 'integer',
        'credit_installments_count' => 'integer',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function isSimple(): bool
    {
        return $this->type === self::TYPE_SIMPLE;
    }

    public function isVariable(): bool
    {
        return $this->type === self::TYPE_VARIABLE;
    }
}
