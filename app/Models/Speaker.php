<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Speaker extends Model
{
    use HasFactory, SoftDeletes;

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
        'title',
        'company',
        'website',
        'social_links',
        'specialties',
        'is_featured',
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
            'social_links' => 'array',
            'specialties' => 'array',
            'is_featured' => 'boolean',
        ];
    }
    
    /**
     * Get photo URLs from folder
     * Files are stored in storage/app/public/speakers/ folder
     */
    public function getPhotoUrls(): array
    {
        $folder = 'speakers';
        $speakerId = $this->id;
        $folderPath = "{$folder}/{$speakerId}";
        
        if (!Storage::disk('public')->exists($folderPath)) {
            return [];
        }
        
        $files = Storage::disk('public')->files($folderPath);
        $urls = [];
        
        foreach ($files as $file) {
            $urls[] = Storage::disk('public')->url($file);
        }
        
        return $urls;
    }
    
    /**
     * Get photo file paths (relative to storage)
     */
    public function getPhotoPaths(): array
    {
        $folder = 'speakers';
        $speakerId = $this->id;
        $folderPath = "{$folder}/{$speakerId}";
        
        if (!Storage::disk('public')->exists($folderPath)) {
            return [];
        }
        
        return Storage::disk('public')->files($folderPath);
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

    /**
     * Get the user who deleted this speaker.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

