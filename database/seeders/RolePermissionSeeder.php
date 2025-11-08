<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('permission_role')->delete();
        DB::table('role_user')->delete();
        DB::table('permissions')->delete();
        DB::table('roles')->delete();

        // Create Roles
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access with all permissions',
            ],
            [
                'name' => 'organizer',
                'display_name' => 'Event Organizer',
                'description' => 'Can create and manage events',
            ],
            [
                'name' => 'attendee',
                'display_name' => 'Attendee',
                'description' => 'Can browse and register for events',
            ],
            [
                'name' => 'vendor',
                'display_name' => 'Vendor',
                'description' => 'Can provide services for events',
            ],
            [
                'name' => 'sponsor',
                'display_name' => 'Sponsor',
                'description' => 'Can sponsor events',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }

        // Create Permissions
        $permissions = [
            // User Management
            ['name' => 'users.view', 'display_name' => 'View Users', 'description' => 'Can view user list', 'module' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Can create new users', 'module' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'description' => 'Can edit user information', 'module' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Can delete users', 'module' => 'users'],

            // Event Management
            ['name' => 'events.view', 'display_name' => 'View Events', 'description' => 'Can view events', 'module' => 'events'],
            ['name' => 'events.create', 'display_name' => 'Create Events', 'description' => 'Can create new events', 'module' => 'events'],
            ['name' => 'events.edit', 'display_name' => 'Edit Events', 'description' => 'Can edit events', 'module' => 'events'],
            ['name' => 'events.delete', 'display_name' => 'Delete Events', 'description' => 'Can delete events', 'module' => 'events'],
            ['name' => 'events.publish', 'display_name' => 'Publish Events', 'description' => 'Can publish events', 'module' => 'events'],

            // Ticket Management
            ['name' => 'tickets.view', 'display_name' => 'View Tickets', 'description' => 'Can view tickets', 'module' => 'tickets'],
            ['name' => 'tickets.create', 'display_name' => 'Create Tickets', 'description' => 'Can create tickets', 'module' => 'tickets'],
            ['name' => 'tickets.edit', 'display_name' => 'Edit Tickets', 'description' => 'Can edit tickets', 'module' => 'tickets'],
            ['name' => 'tickets.delete', 'display_name' => 'Delete Tickets', 'description' => 'Can delete tickets', 'module' => 'tickets'],

            // Order Management
            ['name' => 'orders.view', 'display_name' => 'View Orders', 'description' => 'Can view orders', 'module' => 'orders'],
            ['name' => 'orders.manage', 'display_name' => 'Manage Orders', 'description' => 'Can manage orders', 'module' => 'orders'],

            // Role & Permission Management
            ['name' => 'roles.view', 'display_name' => 'View Roles', 'description' => 'Can view roles', 'module' => 'roles'],
            ['name' => 'roles.manage', 'display_name' => 'Manage Roles', 'description' => 'Can manage roles and permissions', 'module' => 'roles'],

            // Dashboard
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'description' => 'Can access dashboard', 'module' => 'dashboard'],
            ['name' => 'dashboard.analytics', 'display_name' => 'View Analytics', 'description' => 'Can view analytics', 'module' => 'dashboard'],

            // Settings
            ['name' => 'settings.view', 'display_name' => 'View Settings', 'description' => 'Can view settings', 'module' => 'settings'],
            ['name' => 'settings.manage', 'display_name' => 'Manage Settings', 'description' => 'Can manage settings', 'module' => 'settings'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }

        // Assign Permissions to Roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign permissions to roles.
     */
    private function assignPermissionsToRoles(): void
    {
        $admin = Role::where('name', 'admin')->first();
        $organizer = Role::where('name', 'organizer')->first();
        $attendee = Role::where('name', 'attendee')->first();
        $vendor = Role::where('name', 'vendor')->first();
        $sponsor = Role::where('name', 'sponsor')->first();

        // Admin gets all permissions
        if ($admin) {
            $admin->permissions()->attach(Permission::pluck('id'));
        }

        // Organizer permissions
        if ($organizer) {
            $organizer->permissions()->attach(Permission::whereIn('name', [
                'events.view',
                'events.create',
                'events.edit',
                'events.delete',
                'events.publish',
                'tickets.view',
                'tickets.create',
                'tickets.edit',
                'tickets.delete',
                'orders.view',
                'orders.manage',
                'dashboard.view',
                'dashboard.analytics',
            ])->pluck('id'));
        }

        // Attendee permissions (default role for new users)
        if ($attendee) {
            $attendee->permissions()->attach(Permission::whereIn('name', [
                'events.view',
                'tickets.view',
                'dashboard.view',
            ])->pluck('id'));
        }

        // Vendor permissions
        if ($vendor) {
            $vendor->permissions()->attach(Permission::whereIn('name', [
                'events.view',
                'dashboard.view',
            ])->pluck('id'));
        }

        // Sponsor permissions
        if ($sponsor) {
            $sponsor->permissions()->attach(Permission::whereIn('name', [
                'events.view',
                'dashboard.view',
            ])->pluck('id'));
        }
    }
}

