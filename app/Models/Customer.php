<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\CreditPlan;

class Customer extends Model
{
    use HasFactory;

    public const TYPE_INDIVIDUAL = 'individual';
    public const TYPE_COMPANY = 'company';

    protected $fillable = [
        'user_id',
        'type',
        'company_id',
        'first_name',
        'last_name',
        'company_contact_name',
        'email',
        'phone',
        'address',
        'credit_limit',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'is_active'    => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class)->orderByDesc('is_default')->orderBy('id');
    }

    public function defaultAddress()
    {
        return $this->hasOne(CustomerAddress::class)->where('is_default', true);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'favorites')->withTimestamps();
    }

    public function isEmployee(): bool
    {
        return $this->type === self::TYPE_COMPANY && !is_null($this->company_id);
    }

    public function isIndividual(): bool
    {
        return $this->type === self::TYPE_INDIVIDUAL;
    }

    /**
     * Plafond crédit effectif.
     * Override individuel en priorité, sinon héritage du plafond de l'entreprise.
     */
    public function effectiveCreditLimit(): ?float
    {
        if (!is_null($this->credit_limit)) {
            return (float) $this->credit_limit;
        }

        return $this->company?->credit_limit ? (float) $this->company->credit_limit : null;
    }

    /**
     * Montant de crédit déjà engagé (plans actifs non soldés).
     */
    public function usedCredit(): float
    {
        return (float) CreditPlan::whereHas('order', fn($q) => $q->where('customer_id', $this->id))
            ->where('status', 'active')
            ->sum('outstanding_amount');
    }

    /**
     * Crédit restant disponible. Null = pas de plafond défini.
     */
    public function availableCredit(): ?float
    {
        $limit = $this->effectiveCreditLimit();

        if (is_null($limit)) {
            return null;
        }

        return max(0, $limit - $this->usedCredit());
    }

    public function isCompany(): bool
    {
        return $this->type === self::TYPE_COMPANY;
    }

    public function displayName(): string
    {
        if ($this->isCompany()) {
            return $this->company?->name
                ? ($this->company_contact_name ? "{$this->company->name} ({$this->company_contact_name})" : $this->company->name)
                : ($this->company_contact_name ?? "Client entreprise #{$this->id}");
        }

        $full = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        return $full !== '' ? $full : "Client #{$this->id}";
    }
}
