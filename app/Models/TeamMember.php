<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMember extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'team_id',
        'user_id',
        'role',
        'permissions',
        'invited_by',
        'joined_at',
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
            'permissions' => 'array',
            'joined_at' => 'datetime',
        ];
    }

    /**
     * Get the team.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who invited this member.
     */
    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Get the user who deleted this team member.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

