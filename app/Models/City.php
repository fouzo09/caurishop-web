<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ville / préfecture de livraison — référentiel du sélecteur d'adresse.
 */
class City extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Ordre d'affichage du dropdown : sort_order puis alphabétique. */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
