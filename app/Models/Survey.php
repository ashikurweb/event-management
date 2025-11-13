<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'title',
        'description',
        'is_required',
        'opens_at',
        'closes_at',
        'status',
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
            'is_required' => 'boolean',
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
        ];
    }

    /**
     * Get the event.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the questions for this survey.
     */
    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('display_order');
    }

    /**
     * Get the responses for this survey.
     */
    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * Get the user who deleted this survey.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

