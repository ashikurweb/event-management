<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'description',
        'status',
        'reviewed_by',
        'reviewed_at',
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
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Get the user who made the report.
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the user who reviewed the report.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the reportable model.
     */
    public function reportable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who deleted this report.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

