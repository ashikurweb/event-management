<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'logo',
        'website',
        'description',
        'tier',
        'display_order',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the events this sponsor is associated with.
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_sponsors')
            ->withPivot('tier', 'contribution_amount', 'display_order')
            ->withTimestamps();
    }
}

