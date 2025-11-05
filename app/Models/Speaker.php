<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speaker extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'bio',
        'photo',
        'title',
        'company',
        'website',
        'social_links',
        'specialties',
        'is_featured',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'specialties' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Get the user associated with this speaker.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the events this speaker is associated with.
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_speakers')
            ->withPivot('role', 'display_order')
            ->withTimestamps();
    }
}

