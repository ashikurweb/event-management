<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewReply extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'review_id',
        'user_id',
        'comment',
        'deleted_by',
    ];

    /**
     * Get the review.
     */
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Get the user who wrote this reply.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who deleted this reply.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

