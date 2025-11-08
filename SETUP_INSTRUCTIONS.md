# 🚀 EventHub - Setup Instructions (Bangla/English)

## 📋 Database Check - Roles & Permissions

### Existing Roles in Database:
1. **admin** - Full system access
2. **organizer** - Event management
3. **attendee** - Default role (সব নতুন user পাবে)
4. **vendor** - Service providers
5. **sponsor** - Event sponsors

### Role Assignment:
- **New Users (Register)**: Automatically get `attendee` role
- **Organizers**: Manually assign `organizer` role
- **Admins**: Manually assign `admin` role

## 🎯 Step-by-Step Setup Guide

### Step 1: Database Seeder Run করুন

```bash
php artisan db:seed --class=RolePermissionSeeder
```

**কি হবে:**
- 5টি role create হবে
- সব permissions create হবে
- Permissions role-এ assign হবে

### Step 2: Email Configuration (.env)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@eventhub.com
MAIL_FROM_NAME="EventHub"
```

**Gmail এর জন্য:**
1. Google Account → Security → 2-Step Verification enable করুন
2. App Passwords → Generate করুন
3. সেই password `.env`-এ `MAIL_PASSWORD`-এ দিন

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

## 📦 Best Package for Role/Permission

### ✅ Current System (Recommended)
আপনার **custom RBAC system** আছে যা:
- Lightweight
- Customizable
- Already integrated
- No external dependencies

**এই system ব্যবহার করুন!**

### Alternative: Spatie Laravel Permission
যদি ভবিষ্যতে advanced features লাগে:

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

**কিন্তু**: আপনার current system EventHub-এর জন্য যথেষ্ট!

## 🔄 Email Verification Flow

### Registration Process:
1. User register করে
2. **Automatically `attendee` role assign হয়**
3. Email verification email send হয়
4. User login হয় (temporary)
5. Verification notice page দেখায়
6. User email-এ link click করে
7. Email verified → Auto-login → Dashboard-এ redirect

### Routes Created:
- `/email/verify` - Verification notice page
- `/email/verify/{id}/{hash}` - Verification link (signed URL)
- `/email/verification-notification` - Resend email

## 📁 Created Files

### Backend:
1. ✅ `database/seeders/RolePermissionSeeder.php` - Roles & permissions seeder
2. ✅ `app/Notifications/VerifyEmailNotification.php` - Email verification notification
3. ✅ `app/Http/Controllers/Auth/EmailVerificationController.php` - Verification controller
4. ✅ Updated `app/Services/Auth/RegisterService.php` - Role assignment & email verification
5. ✅ Updated `app/Http/Controllers/Auth/Register/RegisterController.php` - Email verification flow
6. ✅ Updated `app/Models/User.php` - MustVerifyEmail interface & sendEmailVerificationNotification

### Frontend:
1. ✅ `resources/js/Pages/Auth/VerifyEmail.vue` - Verification notice page

### Routes:
1. ✅ Updated `routes/auth.php` - Email verification routes
2. ✅ Updated `routes/web.php` - Dashboard with verified middleware

### Documentation:
1. ✅ `ROLE_PERMISSION_GUIDE.md` - Complete guide
2. ✅ `SETUP_INSTRUCTIONS.md` - This file

## 🎯 Quick Commands

### Run Seeder:
```bash
php artisan db:seed --class=RolePermissionSeeder
```

### Create Admin User (Manual):
```bash
php artisan tinker
```
```php
$user = User::create([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'email' => 'admin@eventhub.com',
    'password' => Hash::make('password'),
    'status' => 'active',
    'email_verified_at' => now(),
]);

$adminRole = Role::where('name', 'admin')->first();
$user->roles()->attach($adminRole->id);
```

### Assign Role to User:
```php
$user = User::find(1);
$role = Role::where('name', 'organizer')->first();
$user->roles()->attach($role->id);
```

## ✅ Checklist

- [x] RolePermissionSeeder created
- [x] Email verification notification created
- [x] Email verification controller created
- [x] RegisterService updated (role assignment)
- [x] RegisterController updated (verification flow)
- [x] User model updated (MustVerifyEmail)
- [x] VerifyEmail page created
- [x] Routes configured
- [x] Dashboard protected with verified middleware

## 🚨 Important Notes

1. **Seeder run করতে হবে** - Role/permission data populate করার জন্য
2. **Email configuration** - `.env`-এ mail settings add করতে হবে
3. **Default role** - সব নতুন user automatically `attendee` role পাবে
4. **Email verification** - Verification না হলে dashboard access পাবে না

## 📝 Next Steps

1. ✅ Run seeder: `php artisan db:seed --class=RolePermissionSeeder`
2. ✅ Configure email in `.env`
3. ✅ Test registration flow
4. ✅ Test email verification
5. ✅ Create admin user manually (if needed)

---

**সব কিছু ready! এখন seeder run করুন এবং email configure করুন।**

