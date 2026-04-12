<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditPlan extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'order_id',
        'duration_months',
        'installments_count',
        'total_amount',
        'down_payment_amount',
        'outstanding_amount',
        'status',
    ];

    protected $casts = [
        'duration_months'    => 'integer',
        'installments_count' => 'integer',
        'total_amount'       => 'decimal:2',
        'down_payment_amount'=> 'decimal:2',
        'outstanding_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }
}
