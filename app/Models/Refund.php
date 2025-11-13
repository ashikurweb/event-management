<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'payment_id',
        'order_id',
        'amount',
        'reason',
        'status',
        'refund_transaction_id',
        'processed_by',
        'processed_at',
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
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Get the payment for this refund.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the order for this refund.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who processed this refund.
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the user who deleted this refund.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

