<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Carnet d'adresses de livraison d'un client (espace client du shop).
 */
class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'label',
        'full_name',
        'phone',
        'city',
        'address',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Adresse sur une ligne, pour les récapitulatifs.
     */
    public function inline(): string
    {
        return trim($this->address . ', ' . $this->city);
    }
}
