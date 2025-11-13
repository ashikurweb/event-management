<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyAnswer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'response_id',
        'question_id',
        'answer',
        'deleted_by',
    ];

    /**
     * Get the response.
     */
    public function response()
    {
        return $this->belongsTo(SurveyResponse::class, 'response_id');
    }

    /**
     * Get the question.
     */
    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class);
    }

    /**
     * Get the user who deleted this answer.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

