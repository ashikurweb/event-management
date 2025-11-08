# Custom RBAC System - Usage Guide

## ✅ No Spatie Package Needed!

আপনার **custom Role & Permission system** আছে যা Spatie-এর মতো powerful এবং lightweight!

## 🎯 Available Methods

### User Model Methods

#### Role Checking:
```php
// Check single role
$user->hasRole('admin'); // true/false

// Check any of multiple roles
$user->hasAnyRole(['admin', 'organizer']); // true/false

// Check all roles
$user->hasAllRoles(['admin', 'organizer']); // true/false
```

#### Permission Checking:
```php
// Check single permission (through roles)
$user->hasPermission('events.create'); // true/false

// Check any of multiple permissions
$user->hasAnyPermission(['events.create', 'events.edit']); // true/false

// Check all permissions
$user->hasAllPermissions(['events.create', 'events.edit']); // true/false

// Get all permissions
$user->getPermissions(); // Collection of Permission models

// Get permission names
$user->getPermissionNames(); // ['events.create', 'events.edit', ...]
```

#### Role Management:
```php
// Assign role
$user->assignRole('organizer');
$user->assignRole($roleModel);

// Remove role
$user->removeRole('organizer');

// Sync roles (replace all)
$user->syncRoles(['admin', 'organizer']);
```

### Role Model Methods

#### Permission Management:
```php
// Check permission
$role->hasPermission('events.create');
$role->hasAnyPermission(['events.create', 'events.edit']);

// Assign permission
$role->givePermission('events.create');
$role->givePermission($permissionModel);

// Remove permission
$role->revokePermission('events.create');

// Sync permissions (replace all)
$role->syncPermissions(['events.create', 'events.edit', 'events.delete']);
```

## 🛡️ Middleware Usage

### In Routes:

```php
// Single role
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

// Multiple roles (OR condition - any one)
Route::middleware(['auth', 'role:admin,organizer'])->group(function () {
    Route::get('/events/create', [EventController::class, 'create']);
});

// Single permission
Route::middleware(['auth', 'permission:events.create'])->group(function () {
    Route::post('/events', [EventController::class, 'store']);
});

// Multiple permissions (OR condition - any one)
Route::middleware(['auth', 'permission:events.create,events.edit'])->group(function () {
    Route::get('/events/manage', [EventController::class, 'manage']);
});

// Combined
Route::middleware(['auth', 'verified', 'role:organizer', 'permission:events.create'])->group(function () {
    Route::get('/events/create', [EventController::class, 'create']);
});
```

### In Controllers:

```php
use App\Http\Controllers\Controller;

class EventController extends Controller
{
    public function __construct()
    {
        // Apply to all methods
        $this->middleware('role:organizer,admin');
        
        // Or apply to specific methods
        $this->middleware('permission:events.create')->only(['create', 'store']);
        $this->middleware('permission:events.delete')->only(['destroy']);
    }

    public function create()
    {
        // Check in method
        if (!auth()->user()->hasPermission('events.create')) {
            abort(403, 'You do not have permission to create events.');
        }
        
        return view('events.create');
    }
}
```

## 💻 Usage Examples

### Example 1: Check Role in Blade/View
```php
@if(auth()->user()->hasRole('admin'))
    <a href="/admin">Admin Panel</a>
@endif

@if(auth()->user()->hasAnyRole(['admin', 'organizer']))
    <a href="/events/create">Create Event</a>
@endif
```

### Example 2: Check Permission in Controller
```php
public function store(Request $request)
{
    if (!auth()->user()->hasPermission('events.create')) {
        return redirect()->back()
            ->with('error', 'You do not have permission to create events.');
    }
    
    // Create event logic
}
```

### Example 3: Assign Role Programmatically
```php
// After user registration
$user = User::create([...]);
$user->assignRole('attendee');

// Promote user to organizer
$user->assignRole('organizer');

// User can have multiple roles
$user->assignRole('organizer');
$user->assignRole('vendor');
```

### Example 4: Check Multiple Conditions
```php
$user = auth()->user();

// User must be admin OR organizer AND have events.create permission
if (($user->hasAnyRole(['admin', 'organizer'])) && $user->hasPermission('events.create')) {
    // Allow access
}
```

## 📝 Common Patterns

### Pattern 1: Admin Only Route
```php
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // Admin routes
});
```

### Pattern 2: Organizer + Admin Route
```php
Route::middleware(['auth', 'role:organizer,admin'])->group(function () {
    Route::resource('events', EventController::class);
});
```

### Pattern 3: Permission-Based Route
```php
Route::middleware(['auth', 'permission:events.create'])->group(function () {
    Route::get('/events/create', [EventController::class, 'create']);
    Route::post('/events', [EventController::class, 'store']);
});
```

### Pattern 4: Combined Role + Permission
```php
Route::middleware([
    'auth',
    'verified',
    'role:organizer,admin',
    'permission:events.publish'
])->group(function () {
    Route::post('/events/{id}/publish', [EventController::class, 'publish']);
});
```

## 🎨 Frontend Usage (Vue/Inertia)

### In Vue Components:
```vue
<script setup>
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const user = page.props.auth?.user

// Check role
const isAdmin = user?.roles?.some(role => role.name === 'admin')

// Check permission (you need to pass permissions from backend)
const canCreateEvents = user?.permissions?.includes('events.create')
</script>

<template>
  <div v-if="isAdmin">
    <a href="/admin">Admin Panel</a>
  </div>
  
  <button v-if="canCreateEvents" @click="createEvent">
    Create Event
  </button>
</template>
```

### Share User Data with Permissions:
Update `HandleInertiaRequests.php`:
```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->full_name,
                'email' => $request->user()->email,
                'roles' => $request->user()?->roles->pluck('name')->toArray(),
                'permissions' => $request->user()?->getPermissionNames(),
            ] : null,
        ],
        'flash' => [
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
            'info' => $request->session()->get('info'),
            'warning' => $request->session()->get('warning'),
        ],
    ];
}
```

## ✅ Advantages of Custom System

1. ✅ **No External Dependencies** - Pure Laravel
2. ✅ **Lightweight** - Fast performance
3. ✅ **Fully Customizable** - Modify as needed
4. ✅ **Simple** - Easy to understand
5. ✅ **Already Integrated** - Works with your codebase

## 🚀 Quick Reference

### Check Role:
- `$user->hasRole('admin')`
- `$user->hasAnyRole(['admin', 'organizer'])`
- `$user->hasAllRoles(['admin', 'organizer'])`

### Check Permission:
- `$user->hasPermission('events.create')`
- `$user->hasAnyPermission(['events.create', 'events.edit'])`
- `$user->hasAllPermissions(['events.create', 'events.edit'])`

### Manage Roles:
- `$user->assignRole('organizer')`
- `$user->removeRole('organizer')`
- `$user->syncRoles(['admin', 'organizer'])`

### Middleware:
- `Route::middleware(['role:admin'])`
- `Route::middleware(['permission:events.create'])`

---

**Your custom system is powerful enough! No need for Spatie package.**

