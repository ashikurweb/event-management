<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyQuestion extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'survey_id',
        'question',
        'type',
        'options',
        'is_required',
        'display_order',
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
            'options' => 'array',
            'is_required' => 'boolean',
        ];
    }

    /**
     * Get the survey.
     */
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the answers for this question.
     */
    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    /**
     * Get the user who deleted this question.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

