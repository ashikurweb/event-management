<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventMedia extends Model
{
    use HasFactory, SoftDeletes;

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
        'deleted_by',
    ];

    /**
     * Get the event that owns this media.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who deleted this media.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

