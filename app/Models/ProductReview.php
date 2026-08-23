<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Avis client sur un produit : note de 1 à 5 + commentaire.
 */
class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'customer_id',
        'rating',
        'title',
        'comment',
        'is_verified',
        'is_approved',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'is_verified' => 'boolean',
        'is_approved' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /** Nom affiché de l'auteur : prénom + initiale du nom, comme sur les marketplaces. */
    public function authorName(): string
    {
        $customer = $this->customer;

        if (! $customer) {
            return 'Client CAURISHOP';
        }

        $first = trim((string) $customer->first_name);
        $last  = trim((string) $customer->last_name);

        return trim($first . ' ' . (mb_substr($last, 0, 1) ? mb_strtoupper(mb_substr($last, 0, 1)) . '.' : '')) ?: 'Client CAURISHOP';
    }
}
