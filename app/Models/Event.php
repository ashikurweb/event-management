<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'organizer_id',
        'category_id',
        'title',
        'slug',
        'description',
        'short_description',
        'event_type',
        'status',
        'visibility',
        'start_date',
        'end_date',
        'timezone',
        'registration_start',
        'registration_end',
        'venue_name',
        'venue_address',
        'venue_city',
        'venue_state',
        'venue_country',
        'venue_postal_code',
        'latitude',
        'longitude',
        'meeting_url',
        'meeting_platform',
        'meeting_id',
        'meeting_password',
        'max_attendees',
        'min_attendees',
        'current_attendees',
        'waitlist_enabled',
        'featured_image',
        'banner_image',
        'video_url',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_featured',
        'allow_comments',
        'allow_reviews',
        'require_approval',
        'view_count',
        'share_count',
        'published_at',
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
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'registration_start' => 'datetime',
            'registration_end' => 'datetime',
            'published_at' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'waitlist_enabled' => 'boolean',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'allow_reviews' => 'boolean',
            'require_approval' => 'boolean',
            'uuid' => 'string',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->uuid)) {
                $event->uuid = (string) Str::uuid();
            }
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);
            }
        });
    }

    /**
     * Get the organizer of the event.
     */
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * Get the category of the event.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the tags for the event.
     */
    public function tags()
    {
        return $this->belongsToMany(EventTag::class, 'event_tag_pivot')
            ->withTimestamps();
    }

    /**
     * Get the schedules for the event.
     */
    public function schedules()
    {
        return $this->hasMany(EventSchedule::class)->orderBy('display_order');
    }

    /**
     * Get the FAQs for the event.
     */
    public function faqs()
    {
        return $this->hasMany(EventFaq::class)->orderBy('display_order');
    }

    /**
     * Get the media for the event.
     */
    public function media()
    {
        return $this->hasMany(EventMedia::class)->orderBy('display_order');
    }

    /**
     * Get the ticket types for the event.
     */
    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    /**
     * Get the orders for the event.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the reviews for the event.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Check if event is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if event registration is open.
     */
    public function isRegistrationOpen(): bool
    {
        $now = now();
        $start = $this->registration_start ?? $this->start_date;
        $end = $this->registration_end ?? $this->end_date;

        return $now >= $start && $now <= $end;
    }

    /**
     * Get the user who deleted this event.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

