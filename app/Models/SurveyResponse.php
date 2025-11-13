<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyResponse extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'survey_id',
        'user_id',
        'deleted_by',
    ];

    /**
     * Get the survey.
     */
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the answers for this response.
     */
    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class, 'response_id');
    }

    /**
     * Get the user who deleted this response.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

