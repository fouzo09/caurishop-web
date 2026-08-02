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

    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    /**
     * Retourne le portail d'accueil selon le rôle.
     */
    public function homeRoute(): string
    {
        // Le back-office plateforme reste la destination des équipes CAURISHOP.
        if ($this->isSuperAdmin())      return route('admin.dashboard');
        if ($this->hasRole('employee')) return route('admin.dashboard');

        // Tout le reste est client : particulier ou salarié rattaché à une
        // entreprise. Même espace, seules les entrées de menu diffèrent.
        if ($this->isCustomer())        return route('shop.account.index');
        if ($this->isCompanyAdmin())    return route('shop.account.index');
        if ($this->isCompanyEmployee()) return route('shop.account.index');

        // Aucun rôle reconnu : la boutique publique, jamais le formulaire de
        // connexion — ce qui provoquerait une boucle de redirection.
        return route('home');
    }
}
