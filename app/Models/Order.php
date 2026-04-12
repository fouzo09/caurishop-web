<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, HasUuid;

    public const TYPE_CASH = 'cash';
    public const TYPE_CREDIT = 'credit';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_number',
        'customer_id',
        'order_type',
        'status',
        'total_amount',
        'down_payment',
        'credit_installments_count',
        'created_by',
        'confirmed_at',
        'delivered_at',
    ];

    protected $casts = [
        'total_amount'              => 'decimal:2',
        'down_payment'              => 'decimal:2',
        'credit_installments_count' => 'integer',
        'confirmed_at'              => 'datetime',
        'delivered_at'              => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function creditPlan()
    {
        return $this->hasOne(CreditPlan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCredit(): bool
    {
        return $this->order_type === self::TYPE_CREDIT;
    }

    public function isCash(): bool
    {
        return $this->order_type === self::TYPE_CASH;
    }
}
