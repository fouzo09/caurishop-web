<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory, HasUuid;

    public const METHOD_CASH = 'cash';
    public const METHOD_TRANSFER = 'transfer';
    public const METHOD_MOBILE_MONEY = 'mobile_money';
    public const METHOD_CARD = 'card';
    public const METHOD_OTHER = 'other';

    protected $fillable = [
        'customer_id',
        'order_id',
        'credit_plan_id',
        'amount',
        'payment_date',
        'method',
        'reference',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creditPlan()
    {
        return $this->belongsTo(CreditPlan::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
