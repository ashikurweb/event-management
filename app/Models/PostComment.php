<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'comment',
    ];

    /**
     * Get the post.
     */
    public function post()
    {
        return $this->belongsTo(EventPost::class, 'post_id');
    }

    /**
     * Get the user who wrote this comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

