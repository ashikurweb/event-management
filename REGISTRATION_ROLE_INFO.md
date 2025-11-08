# 📋 Registration Role Information

## 🎯 Default Role for New Users

### **Role Name: `attendee`**

যখন কেউ normal registration করবে, তখন **automatically `attendee` role** assign হবে।

## 📍 Code Location

**File:** `app/Services/Auth/RegisterService.php`

```php
protected function assignDefaultRole(User $user): void
{
    try {
        $user->assignRole('attendee');  // ← এখানে attendee role assign হচ্ছে
        Log::info('Default role assigned to user', [
            'user_id' => $user->id,
            'role' => 'attendee',
        ]);
    } catch (\Exception $e) {
        Log::warning('Default role (attendee) not found...');
    }
}
```

## 🎭 Attendee Role Details

### Role Information:
- **Name:** `attendee`
- **Display Name:** `Attendee`
- **Description:** `Can browse and register for events`

### Permissions (Attendee Role-এ কি কি permissions আছে):

#### ✅ View Permissions:
- `events.view` - View events
- `tickets.view` - View tickets
- `dashboard.access` - Access dashboard

#### ❌ No Permissions:
- ❌ Cannot create events
- ❌ Cannot edit events
- ❌ Cannot delete events
- ❌ Cannot publish events
- ❌ Cannot manage users
- ❌ Cannot manage orders

## 📊 Database Structure

### Roles Table:
```sql
SELECT * FROM roles WHERE name = 'attendee';
```

**Result:**
- `id`: (auto-generated)
- `name`: `attendee`
- `display_name`: `Attendee`
- `description`: `Can browse and register for events`
- `created_at`: (timestamp)
- `updated_at`: (timestamp)

### Role-User Relationship:
```sql
SELECT * FROM role_user WHERE user_id = [user_id];
```

### Role-Permission Relationship:
```sql
SELECT p.name, p.display_name 
FROM permissions p
JOIN permission_role pr ON p.id = pr.permission_id
JOIN roles r ON pr.role_id = r.id
WHERE r.name = 'attendee';
```

## 🔍 How to Check in Database

### Option 1: Using Tinker
```bash
php artisan tinker
```

```php
// Check attendee role
$role = Role::where('name', 'attendee')->first();
$role->permissions->pluck('name');

// Check a specific user's role
$user = User::find(1);
$user->roles->pluck('name');
$user->getPermissionNames();
```

### Option 2: Direct SQL Query
```sql
-- Check attendee role
SELECT * FROM roles WHERE name = 'attendee';

-- Check attendee permissions
SELECT p.name, p.display_name, p.description
FROM permissions p
INNER JOIN permission_role pr ON p.id = pr.permission_id
INNER JOIN roles r ON pr.role_id = r.id
WHERE r.name = 'attendee';

-- Check user's role
SELECT r.name, r.display_name
FROM roles r
INNER JOIN role_user ru ON r.id = ru.role_id
INNER JOIN users u ON ru.user_id = u.id
WHERE u.email = 'user@example.com';
```

## 🔄 Registration Flow

1. **User fills registration form** → `Register.vue`
2. **Form submitted** → `RegisterController@register`
3. **User created** → `RegisterService@createUser`
4. **Default role assigned** → `RegisterService@assignDefaultRole('attendee')`
5. **Email verification sent** → `RegisterService@sendEmailVerification`
6. **User logged in** → Redirected to verification notice page

## 📝 All Available Roles

### 1. **admin**
- Full system access
- All permissions

### 2. **organizer**
- Can create and manage events
- Event management permissions

### 3. **attendee** ⭐ (Default for new users)
- Can browse and register for events
- View permissions only

### 4. **vendor**
- Can provide services for events
- Limited permissions

### 5. **sponsor**
- Can sponsor events
- Limited permissions

## ⚠️ Important Notes

1. **Seeder must be run** - Without running `RolePermissionSeeder`, `attendee` role won't exist
2. **Error handling** - If role not found, it logs a warning but doesn't break registration
3. **Manual assignment** - Admin can later change user's role manually

## 🔧 Change Default Role

যদি default role change করতে চান:

**File:** `app/Services/Auth/RegisterService.php`

```php
protected function assignDefaultRole(User $user): void
{
    try {
        // Change 'attendee' to your desired role
        $user->assignRole('organizer');  // Example: change to organizer
        // ...
    }
}
```

## ✅ Verification Checklist

- [ ] `RolePermissionSeeder` run করা হয়েছে
- [ ] `attendee` role database-এ আছে
- [ ] `attendee` role-এ permissions assign করা হয়েছে
- [ ] Registration test করা হয়েছে
- [ ] New user-এর role check করা হয়েছে

---

**Summary:** Normal registration-এ সব user **`attendee` role** পাবে automatically।

