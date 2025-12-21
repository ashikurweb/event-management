<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TKT-' . strtoupper(Str::random(10));
            }
            if (empty($ticket->qr_code)) {
                $ticket->qr_code = Str::uuid()->toString();
            }
        });
    }

    /**
     * Scope a query to search tickets.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function ($query, $search) {
            $query->where('ticket_number', 'like', "%{$search}%")
                ->orWhere('attendee_first_name', 'like', "%{$search}%")
                ->orWhere('attendee_last_name', 'like', "%{$search}%")
                ->orWhere('attendee_email', 'like', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Map ticket_number to name for activity logging.
     */
    public function getNameAttribute(): string
    {
        return $this->ticket_number;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ticket_number',
        'order_id',
        'order_item_id',
        'ticket_type_id',
        'event_id',
        'user_id',
        'attendee_first_name',
        'attendee_last_name',
        'attendee_email',
        'attendee_phone',
        'qr_code',
        'barcode',
        'status',
        'checked_in',
        'checked_in_at',
        'checked_in_by',
        'transferred_to',
        'transferred_at',
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
            'checked_in' => 'boolean',
            'checked_in_at' => 'datetime',
            'transferred_at' => 'datetime',
        ];
    }


    /**
     * Get the order for this ticket.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order item for this ticket.
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the ticket type.
     */
    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    /**
     * Get the event.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user (buyer).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who checked in this ticket.
     */
    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * Get the user this ticket was transferred to.
     */
    public function transferredToUser()
    {
        return $this->belongsTo(User::class, 'transferred_to');
    }

    /**
     * Get attendee full name.
     */
    public function getAttendeeFullNameAttribute(): ?string
    {
        if ($this->attendee_first_name || $this->attendee_last_name) {
            return trim("{$this->attendee_first_name} {$this->attendee_last_name}");
        }
        return null;
    }

    /**
     * Check if ticket is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->checked_in;
    }

    /**
     * Get the user who deleted this ticket.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

