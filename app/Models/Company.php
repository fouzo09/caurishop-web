<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'registration_number',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'credit_limit',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
