<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'raison_sociale',
        'status',
        'registration_number',
        'email',
        'phone',
        'address',
        'quartier',
        'city',
        'country',
        'credit_limit',
        'is_active',
        // Gérant
        'gerant_nom',
        'gerant_prenom',
        'gerant_tel',
        'gerant_piece',
        'gerant_adresse',
        // Infos complémentaires
        'date_creation',
        'nombre_employes',
        // Documents
        'doc_rccm',
        'doc_nif',
        'doc_statuts',
        'doc_cni',
        'doc_patente',
        'doc_domicile',
    ];

    protected $casts = [
        'credit_limit'  => 'decimal:2',
        'is_active'     => 'boolean',
        'date_creation' => 'date',
    ];

    public function isPending(): bool   { return $this->status === self::STATUS_PENDING; }
    public function isApproved(): bool  { return $this->status === self::STATUS_APPROVED; }
    public function isRejected(): bool  { return $this->status === self::STATUS_REJECTED; }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
