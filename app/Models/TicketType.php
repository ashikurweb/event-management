<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketType extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * Scope a query to search ticket types.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%");
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'name',
        'description',
        'type',
        'price',
        'currency',
        'quantity_total',
        'quantity_sold',
        'quantity_reserved',
        'min_per_order',
        'max_per_order',
        'sale_start',
        'sale_end',
        'is_visible',
        'requires_approval',
        'absorb_fees',
        'display_order',
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
            'price' => 'decimal:2',
            'sale_start' => 'datetime',
            'sale_end' => 'datetime',
            'is_visible' => 'boolean',
            'requires_approval' => 'boolean',
            'absorb_fees' => 'boolean',
        ];
    }

    /**
     * Get the event that owns this ticket type.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the tickets of this type.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the order items for this ticket type.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if ticket type is available.
     */
    public function isAvailable(): bool
    {
        if (!$this->is_visible) {
            return false;
        }

        $now = now();
        if ($this->sale_start && $now < $this->sale_start) {
            return false;
        }
        if ($this->sale_end && $now > $this->sale_end) {
            return false;
        }

        if ($this->quantity_total !== null) {
            $available = $this->quantity_total - $this->quantity_sold - $this->quantity_reserved;
            return $available > 0;
        }

        return true;
    }

    /**
     * Get available quantity.
     */
    public function getAvailableQuantityAttribute(): ?int
    {
        if ($this->quantity_total === null) {
            return null;
        }

        return max(0, $this->quantity_total - $this->quantity_sold - $this->quantity_reserved);
    }

    /**
     * Get the user who deleted this ticket type.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

