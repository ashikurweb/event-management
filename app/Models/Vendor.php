<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'company',
        'email',
        'phone',
        'logo',
        'description',
        'website',
        'category',
        'rating',
        'is_verified',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Get the events this vendor is associated with.
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_vendors')
            ->withPivot('booth_number', 'booth_size', 'package_type', 'cost', 'status', 'notes')
            ->withTimestamps();
    }
}

