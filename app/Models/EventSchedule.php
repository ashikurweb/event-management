<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventSchedule extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'location',
        'speaker_id',
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
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    /**
     * Get the event that owns this schedule.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the speaker for this schedule.
     */
    public function speaker()
    {
        return $this->belongsTo(User::class, 'speaker_id');
    }

    /**
     * Get the user who deleted this schedule.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

