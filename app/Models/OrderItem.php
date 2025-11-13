<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'ticket_type_id',
        'quantity',
        'unit_price',
        'total_price',
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
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    /**
     * Get the order that owns this item.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the ticket type for this item.
     */
    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    /**
     * Get the tickets for this order item.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the user who deleted this order item.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

