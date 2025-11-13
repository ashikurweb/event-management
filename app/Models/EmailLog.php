<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailLog extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'email',
        'subject',
        'template_id',
        'status',
        'error_message',
        'opened_at',
        'clicked_at',
        'sent_at',
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
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the email template.
     */
    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    /**
     * Get the user who deleted this email log.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

