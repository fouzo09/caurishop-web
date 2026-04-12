<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'company_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function customer()
    {
        return $this->hasOne(\App\Models\Customer::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isCompanyAdmin(): bool
    {
        return $this->hasRole('company_admin');
    }

    public function isCompanyEmployee(): bool
    {
        return $this->hasRole('company_employee');
    }

    /**
     * Retourne le portail d'accueil selon le rôle.
     */
    public function homeRoute(): string
    {
        if ($this->isSuperAdmin())     return route('admin.dashboard');
        if ($this->isCompanyAdmin())   return route('company.dashboard');
        if ($this->isCompanyEmployee()) return route('portal.dashboard');
        return route('login');
    }
}
