<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventAnalytic extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'date',
        'page_views',
        'unique_visitors',
        'tickets_sold',
        'revenue',
        'registrations',
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
            'date' => 'date',
            'revenue' => 'decimal:2',
        ];
    }

    /**
     * Get the event.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who deleted this analytic.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

