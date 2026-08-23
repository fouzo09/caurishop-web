<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Carnet d'adresses de livraison d'un client (espace client du shop).
 * Adresse = ville (référentiel) + quartier + précision facultative.
 */
class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'label',
        'full_name',
        'phone',
        'city_id',
        'quartier',
        'precision',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /** Nom de la ville, vide si la ville a été retirée du référentiel. */
    public function cityName(): string
    {
        return (string) ($this->city?->name ?? '');
    }

    /** Quartier + précision, sans la ville : « Almamya — en face de la pharmacie ». */
    public function street(): string
    {
        return trim($this->quartier . ($this->precision ? ' — ' . $this->precision : ''));
    }

    /**
     * Adresse sur une ligne, pour les récapitulatifs.
     */
    public function inline(): string
    {
        return trim(implode(', ', array_filter([$this->street(), $this->cityName()])));
    }
}
