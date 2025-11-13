<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'rating',
        'title',
        'comment',
        'status',
        'is_verified_attendee',
        'helpful_count',
        'reported_count',
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
            'is_verified_attendee' => 'boolean',
        ];
    }

    /**
     * Get the event for this review.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who wrote this review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the replies to this review.
     */
    public function replies()
    {
        return $this->hasMany(ReviewReply::class);
    }

    /**
     * Check if review is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Get the user who deleted this review.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

