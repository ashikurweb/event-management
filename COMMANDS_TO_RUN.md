# 🚀 Commands to Run - EventHub Setup

## ✅ Step-by-Step Commands

### Step 1: Database Seeder Run করুন (Role & Permissions)

```bash
php artisan db:seed --class=RolePermissionSeeder
```

**কি হবে:**
- 5টি role create হবে (admin, organizer, attendee, vendor, sponsor)
- সব permissions create হবে
- Permissions role-এ assign হবে

**Output দেখাবে:**
```
Seeding: Database\Seeders\RolePermissionSeeder
Seeded:  Database\Seeders\RolePermissionSeeder
```

### Step 2: Clear Cache (Optional কিন্তু Recommended)

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 3: Test Email Configuration (Optional)

```bash
php artisan tinker
```

তারপর tinker-এ:
```php
Mail::raw('Test email from EventHub', function ($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});
```

## 📋 Quick Command Reference

### Database Commands:
```bash
# Run migrations (if not done)
php artisan migrate

# Run seeder
php artisan db:seed --class=RolePermissionSeeder

# Run all seeders
php artisan db:seed

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

### Cache Commands:
```bash
# Clear all cache
php artisan optimize:clear

# Or individually
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Check Routes:
```bash
# List all routes
php artisan route:list

# Check specific route
php artisan route:list --name=verification
```

## 🎯 Must Run Commands (Required)

### 1. Role & Permission Seeder (MUST RUN):
```bash
php artisan db:seed --class=RolePermissionSeeder
```

**এই command run না করলে:**
- Roles create হবে না
- Permissions create হবে না
- New user registration-এ error হতে পারে (role not found)

## ✅ Verification Commands

### Check if Roles Created:
```bash
php artisan tinker
```
```php
// Check roles
Role::all()->pluck('name');

// Check permissions
Permission::count();

// Check a specific role
$role = Role::where('name', 'attendee')->first();
$role->permissions->pluck('name');
```

### Check User Role:
```php
$user = User::find(1);
$user->roles->pluck('name');
$user->getPermissionNames();
```

## 🔧 Troubleshooting Commands

### If Seeder Fails:
```bash
# Check database connection
php artisan migrate:status

# Check if tables exist
php artisan tinker
```
```php
Schema::hasTable('roles');
Schema::hasTable('permissions');
Schema::hasTable('role_user');
Schema::hasTable('permission_role');
```

### Reset Everything:
```bash
# WARNING: This will delete all data!
php artisan migrate:fresh --seed
```

## 📝 Complete Setup Sequence

```bash
# 1. Run seeder (MUST)
php artisan db:seed --class=RolePermissionSeeder

# 2. Clear cache (Recommended)
php artisan optimize:clear

# 3. Test (Optional)
php artisan tinker
# Then test role/permission queries
```

## ⚠️ Important Notes

1. **Seeder run করতে হবে** - Without this, roles/permissions won't exist
2. **Email config** - `.env`-এ mail settings add করতে হবে
3. **Cache clear** - After seeder, cache clear করলে ভালো

---

**সবচেয়ে গুরুত্বপূর্ণ command:**
```bash
php artisan db:seed --class=RolePermissionSeeder
```

এই command run করুন এবং সব setup হয়ে যাবে!

