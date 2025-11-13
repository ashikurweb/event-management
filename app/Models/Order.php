<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'event_id',
        'subtotal',
        'tax_amount',
        'service_fee',
        'discount_amount',
        'total_amount',
        'currency',
        'status',
        'payment_status',
        'buyer_email',
        'buyer_first_name',
        'buyer_last_name',
        'buyer_phone',
        'promo_code_id',
        'completed_at',
        'cancelled_at',
        'refunded_at',
        'deleted_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(12));
            }
        });
    }

    /**
     * Get the user that placed the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event for this order.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the order items.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the tickets for this order.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the payments for this order.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the promo code used.
     */
    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    /**
     * Check if order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' && $this->payment_status === 'paid';
    }

    /**
     * Get the user who deleted this order.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

