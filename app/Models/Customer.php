<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    public const TYPE_INDIVIDUAL = 'individual';
    public const TYPE_COMPANY = 'company';

    protected $fillable = [
        'type',
        'company_id',
        'first_name',
        'last_name',
        'company_contact_name',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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
