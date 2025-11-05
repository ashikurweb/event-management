<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventMedia extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'title',
        'description',
        'display_order',
    ];

    /**
     * Get the event that owns this media.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}

