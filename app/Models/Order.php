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

    public const STATUS_PENDING_PAYMENT  = 'pending_payment';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
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
        // Champs du parcours public (livraison / paiement)
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'delivery_method',
        'payment_method',
        'payment_status',
        'payment_reference',
        'delivery_fee',
        'discount_amount',
    ];

    protected $casts = [
        'total_amount'              => 'decimal:2',
        'down_payment'              => 'decimal:2',
        'delivery_fee'              => 'decimal:2',
        'discount_amount'           => 'decimal:2',
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

    /**
     * Montant net réellement dû (commande publique) :
     * total des lignes − remise + frais de livraison.
     */
    public function netTotal(): float
    {
        return (float) $this->total_amount
            - (float) ($this->discount_amount ?? 0)
            + (float) ($this->delivery_fee ?? 0);
    }
}
