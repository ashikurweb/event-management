# Role & Permission System Guide - EventHub

## 📋 Overview

EventHub uses a custom Role-Based Access Control (RBAC) system. This guide explains the roles, permissions, and how to set up the system.

## 🎭 Available Roles

### 1. **Admin** (Administrator)
- **Description**: Full system access with all permissions
- **Who gets it**: System administrators, super users
- **Permissions**: All permissions (full access)

### 2. **Organizer** (Event Organizer)
- **Description**: Can create and manage events
- **Who gets it**: Event organizers, event managers
- **Permissions**:
  - View, create, edit, delete, publish events
  - Manage tickets
  - View and manage orders
  - Access dashboard and analytics

### 3. **Attendee** (Default Role)
- **Description**: Can browse and register for events
- **Who gets it**: **All new registered users automatically get this role**
- **Permissions**:
  - View events
  - View tickets
  - Access dashboard

### 4. **Vendor** (Service Provider)
- **Description**: Can provide services for events
- **Who gets it**: Vendors, service providers
- **Permissions**:
  - View events
  - Access dashboard

### 5. **Sponsor** (Event Sponsor)
- **Description**: Can sponsor events
- **Who gets it**: Sponsors, advertisers
- **Permissions**:
  - View events
  - Access dashboard

## 🔐 Permission Structure

Permissions are organized by modules:

### User Management
- `users.view` - View user list
- `users.create` - Create new users
- `users.edit` - Edit user information
- `users.delete` - Delete users

### Event Management
- `events.view` - View events
- `events.create` - Create new events
- `events.edit` - Edit events
- `events.delete` - Delete events
- `events.publish` - Publish events

### Ticket Management
- `tickets.view` - View tickets
- `tickets.create` - Create tickets
- `tickets.edit` - Edit tickets
- `tickets.delete` - Delete tickets

### Order Management
- `orders.view` - View orders
- `orders.manage` - Manage orders

### Role & Permission Management
- `roles.view` - View roles
- `roles.manage` - Manage roles and permissions

### Dashboard
- `dashboard.view` - Access dashboard
- `dashboard.analytics` - View analytics

### Settings
- `settings.view` - View settings
- `settings.manage` - Manage settings

## 🚀 Setup Instructions

### Step 1: Run Database Migrations

```bash
php artisan migrate
```

### Step 2: Seed Roles and Permissions

```bash
php artisan db:seed --class=RolePermissionSeeder
```

Or run all seeders:

```bash
php artisan db:seed
```

This will create:
- 5 roles (admin, organizer, attendee, vendor, sponsor)
- All permissions
- Assign permissions to roles

### Step 3: Assign Roles to Users

#### Option 1: Via Code (Recommended for new registrations)
New users automatically get the `attendee` role during registration.

#### Option 2: Via Database
```php
$user = User::find(1);
$role = Role::where('name', 'organizer')->first();
$user->roles()->attach($role->id);
```

#### Option 3: Via Tinker
```bash
php artisan tinker
```
```php
$user = User::find(1);
$role = Role::where('name', 'admin')->first();
$user->roles()->attach($role->id);
```

## 💻 Usage in Code

### Check User Role

```php
// Check if user has a specific role
if ($user->hasRole('admin')) {
    // User is admin
}

// Check if user has any of the given roles
if ($user->hasAnyRole(['admin', 'organizer'])) {
    // User is admin or organizer
}
```

### Check Role Permissions

```php
$role = Role::where('name', 'organizer')->first();
if ($role->hasPermission('events.create')) {
    // Role has permission
}
```

### Middleware Example

```php
// In routes/web.php or routes/api.php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin only routes
});

Route::middleware(['auth', 'role:organizer,admin'])->group(function () {
    // Organizer or Admin routes
});
```

## 📦 Best Package Recommendation

### Current System
You already have a **custom RBAC system** that works well for your needs. It's:
- ✅ Lightweight
- ✅ Customizable
- ✅ Already integrated
- ✅ No external dependencies

### Alternative: Spatie Laravel Permission (If Needed Later)

If you need more advanced features in the future, consider:
**Package**: `spatie/laravel-permission`

**Installation**:
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

**Why Spatie?**
- ✅ Most popular Laravel permission package
- ✅ Well-maintained and tested
- ✅ Supports roles, permissions, guards
- ✅ Caching support
- ✅ Blade directives
- ✅ Middleware support

**But**: Your current system is sufficient for EventHub's needs!

## 🔄 Email Verification Flow

### Registration Process:
1. User registers → Account created
2. **Default role "attendee" assigned automatically**
3. Email verification sent
4. User redirected to verification notice page
5. User clicks verification link in email
6. Email verified → Auto-login → Redirect to dashboard

### Routes:
- `/email/verify` - Verification notice page
- `/email/verify/{id}/{hash}` - Verification link (signed URL)
- `/email/verification-notification` - Resend verification email

## 📝 Email Configuration

### Step 1: Configure .env

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@eventhub.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 2: For Gmail
1. Enable 2-Step Verification
2. Generate App Password: https://myaccount.google.com/apppasswords
3. Use App Password in `MAIL_PASSWORD`

### Step 3: Test Email

```bash
php artisan tinker
```
```php
Mail::raw('Test email', function ($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});
```

## 🎯 Quick Reference

### Default Role Assignment
- **New users**: Automatically get `attendee` role
- **Location**: `app/Services/Auth/RegisterService.php` → `assignDefaultRole()`

### Change Default Role
Edit `app/Services/Auth/RegisterService.php`:
```php
$defaultRole = Role::where('name', 'your-role-name')->first();
```

### Add New Role
1. Add to `RolePermissionSeeder.php`
2. Run seeder: `php artisan db:seed --class=RolePermissionSeeder`

### Add New Permission
1. Add to `RolePermissionSeeder.php`
2. Assign to roles in `assignPermissionsToRoles()`
3. Run seeder

## ✅ Checklist

- [x] Database tables created (roles, permissions, role_user, permission_role)
- [x] Models created (Role, Permission, User with relationships)
- [x] Seeder created (RolePermissionSeeder)
- [x] Default role assignment on registration
- [x] Email verification system
- [x] Verification routes and controller
- [x] Verification page (VerifyEmail.vue)

## 🐛 Troubleshooting

### Roles not found?
```bash
php artisan db:seed --class=RolePermissionSeeder
```

### Email not sending?
1. Check `.env` mail configuration
2. Check mail logs: `storage/logs/laravel.log`
3. Test with: `php artisan tinker` → Mail::raw(...)

### User not getting default role?
- Check `RegisterService::assignDefaultRole()`
- Ensure seeder has been run
- Check logs: `storage/logs/laravel.log`

## 📚 Next Steps

1. ✅ Run seeder to populate roles and permissions
2. ✅ Configure email settings
3. ✅ Test registration flow
4. ✅ Test email verification
5. ✅ Create admin user manually (if needed)
6. ✅ Add role-based middleware to protected routes

---

**Need Help?** Check Laravel documentation or contact the development team.

