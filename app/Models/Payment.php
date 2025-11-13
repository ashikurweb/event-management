<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'transaction_id',
        'payment_gateway',
        'payment_method',
        'amount',
        'currency',
        'status',
        'gateway_response',
        'failure_reason',
        'paid_at',
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
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * Get the order for this payment.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the refunds for this payment.
     */
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get the user who deleted this payment.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

