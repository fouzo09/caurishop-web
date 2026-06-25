<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DjomyTransaction extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_SUCCESS   = 'success';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'installment_id',
        'order_id',
        'customer_id',
        'djomy_transaction_id',
        'merchant_reference',
        'amount',
        'status',
        'payment_method',
        'payer_identifier',
        'djomy_response',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'djomy_response'  => 'array',
        'order_id'        => 'string', // UUID
    ];

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
