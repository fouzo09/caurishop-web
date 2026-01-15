<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Installment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';
    public const STATUS_LATE = 'late';

    protected $fillable = [
        'credit_plan_id',
        'installment_number',
        'due_date',
        'amount_due',
        'amount_paid',
        'status',
    ];

    protected $casts = [
        'installment_number' => 'integer',
        'due_date' => 'date',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function creditPlan()
    {
        return $this->belongsTo(CreditPlan::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function remainingAmount(): string
    {
        // retourne une string décimale (cast decimal)
        $remaining = (float) $this->amount_due - (float) $this->amount_paid;
        return number_format(max($remaining, 0), 2, '.', '');
    }

    public function isLate(): bool
    {
        return $this->status !== self::STATUS_PAID
            && $this->due_date instanceof Carbon
            && $this->due_date->isPast();
    }
}
