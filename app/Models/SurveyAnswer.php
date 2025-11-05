<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyAnswer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'response_id',
        'question_id',
        'answer',
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
}

