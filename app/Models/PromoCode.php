<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoCode extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'applicable_to',
        'max_uses',
        'max_uses_per_user',
        'current_uses',
        'min_order_amount',
        'valid_from',
        'valid_until',
        'is_active',
        'created_by',
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
            'discount_value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user who created this promo code.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the events this promo code applies to.
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'promo_code_events')
            ->withTimestamps();
    }

    /**
     * Get the usage records for this promo code.
     */
    public function usages()
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    /**
     * Get the orders that used this promo code.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if promo code is valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($now < $this->valid_from || $now > $this->valid_until) {
            return false;
        }

        if ($this->max_uses !== null && $this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount.
     */
    public function calculateDiscount(float $orderAmount): float
    {
        if (!$this->isValid()) {
            return 0;
        }

        if ($this->min_order_amount !== null && $orderAmount < $this->min_order_amount) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            return ($orderAmount * $this->discount_value) / 100;
        } elseif ($this->discount_type === 'fixed') {
            return min($this->discount_value, $orderAmount);
        } else { // free_ticket
            return $orderAmount; // Full discount
        }
    }

    /**
     * Get the user who deleted this promo code.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

