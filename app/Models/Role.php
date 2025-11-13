<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'deleted_by',
    ];

    /**
     * Get the users that have this role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withTimestamps();
    }

    /**
     * Get the permissions for this role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
            ->withTimestamps();
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Check if role has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions()->whereIn('name', $permissions)->exists();
    }

    /**
     * Assign a permission to the role.
     */
    public function givePermission(string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }

        if (!$this->hasPermission($permission->name)) {
            $this->permissions()->attach($permission->id);
        }
    }

    /**
     * Remove a permission from the role.
     */
    public function revokePermission(string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission->id);
    }

    /**
     * Sync role permissions (replace all permissions with the given ones).
     */
    public function syncPermissions(array $permissions): void
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::where('name', $permission)->firstOrFail()->id;
            }
            return $permission instanceof Permission ? $permission->id : $permission;
        })->toArray();

        $this->permissions()->sync($permissionIds);
    }

    /**
     * Get the user who deleted this role.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

