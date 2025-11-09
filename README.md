# 🎉 Event Management System

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

<p align="center">
  <strong>Modern Event Management Platform built with Laravel 12 & Vue 3</strong>
</p>

---

## 📋 Table of Contents

- [System Requirements](#-system-requirements)
- [Installation Guide](#-installation-guide)
- [Configuration](#-configuration)
- [Database Setup](#-database-setup)
- [Running the Application](#-running-the-application)
- [Project Structure](#-project-structure)
- [Features](#-features)
- [Troubleshooting](#-troubleshooting)

---

## 🔧 System Requirements

Before you begin, ensure you have the following installed on your system:

- **PHP** >= 8.2
- **Composer** >= 2.8.2
- **Node.js** >= 22.0
- **npm** >= 11.4.2
- **MySQL** >= 8.0
- **Git**

---

## 🚀 Installation Guide

### Step 1: Clone the Repository

```bash
git clone https://github.com/ashikurweb/event-management.git
cd event-management
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Install Node Dependencies

```bash
npm install
```

### Step 4: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 5: Configure Database

Open `.env` file and update the database configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=event_management
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 6: Run Migrations and Seeders

**Option 1: Using Custom Command (Recommended)**
```bash
php artisan custom:run
```

**Option 2: Manual Steps**
```bash
# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed
```

### Step 7: Build Frontend Assets

**For Development:**
```bash
npm run dev
```

**For Production:**
```bash
npm run build
```

### Step 8: Access the Application

The application will be available at your configured server URL (e.g., `http://event-management.test` or your virtual host).

---

## ⚙️ Configuration

### Application Settings

Update these settings in `.env` file:

```env
APP_NAME="Event Management System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Mail Configuration

For email functionality, configure your mail settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@eventhub.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Social Authentication

Configure OAuth providers in `.env`:

```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

FACEBOOK_CLIENT_ID=your_facebook_client_id
FACEBOOK_CLIENT_SECRET=your_facebook_client_secret
FACEBOOK_REDIRECT_URI=http://localhost:8000/auth/facebook/callback

GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback
```

---

## 🗄️ Database Setup

### Create Database

```sql
CREATE DATABASE event_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Run Migrations

```bash
php artisan migrate:fresh
```

### Seed Database

The seeder will create:
- **Roles**: admin, organizer, attendee, vendor, sponsor
- **Permissions**: Based on role hierarchy
- **Default Configuration**: Role settings

```bash
php artisan db:seed
```

### Create Admin User (Optional)

```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'email' => 'admin@eventhub.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password'),
    'status' => 'active',
    'email_verified_at' => now(),
]);

$adminRole = \App\Models\Role::where('name', 'admin')->first();
$user->assignRole($adminRole);
```

---

## 🏃 Running the Application

### Development Mode

**Start Vite Dev Server:**
```bash
npm run dev
```

**Access the application:**
- Frontend: Your configured server URL (e.g., `http://localhost:8000` or virtual host)
- Vite HMR: Automatically handled by Vite

### Production Mode

```bash
# Build assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📁 Project Structure

```
event-management/
├── app/
│   ├── Console/Commands/      # Custom Artisan Commands
│   ├── Http/
│   │   ├── Controllers/        # Application Controllers
│   │   ├── Middleware/         # Custom Middleware
│   │   └── Requests/           # Form Request Validation
│   ├── Models/                 # Eloquent Models
│   ├── Notifications/          # Email Notifications
│   ├── Providers/              # Service Providers
│   └── Services/               # Business Logic Services
├── config/                     # Configuration Files
├── database/
│   ├── migrations/             # Database Migrations
│   └── seeders/               # Database Seeders
├── public/                     # Public Assets
├── resources/
│   ├── css/                    # Stylesheets
│   ├── js/                     # JavaScript/Vue Components
│   │   ├── Components/         # Reusable Vue Components
│   │   ├── Composables/        # Vue Composables
│   │   ├── Layouts/           # Vue Layouts
│   │   ├── Pages/              # Vue Pages
│   │   ├── Stores/            # Pinia Stores
│   │   └── utils/              # Utility Functions
│   └── views/                  # Blade Templates
├── routes/                     # Route Definitions
└── storage/                     # Storage Directory
```

---

## ✨ Features

### Authentication & Authorization
- ✅ User Registration & Login
- ✅ Email Verification
- ✅ Social Authentication (Google, Facebook, GitHub)
- ✅ Role-Based Access Control (RBAC)
- ✅ Permission Management

### Event Management
- ✅ Event Creation & Management
- ✅ Event Categories & Tags
- ✅ Event Scheduling
- ✅ Speaker Management
- ✅ Venue Management

### User Management
- ✅ User Profiles
- ✅ Role Assignment
- ✅ Activity Logging
- ✅ Notification Preferences

### Additional Features
- ✅ Password Reset (OTP-based)
- ✅ Email Notifications
- ✅ Theme Support (Light/Dark)
- ✅ Responsive Design

---

## 🛠️ Troubleshooting

### Issue: Composer install fails

**Solution:**
```bash
composer clear-cache
composer install --no-cache
```

### Issue: npm install fails

**Solution:**
```bash
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
```

### Issue: Database connection error

**Solution:**
- Check database credentials in `.env`
- Ensure MySQL/PostgreSQL service is running
- Verify database exists

### Issue: Permission denied errors

**Solution:**
```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Or set permissions
sudo chmod -R 775 storage bootstrap/cache
```


### Issue: Vite assets not loading

**Solution:**
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Rebuild assets
npm run build
```

---

## 📝 Quick Commands Reference

```bash
# Database
php artisan migrate:fresh          # Fresh migration
php artisan db:seed               # Seed database
php artisan custom:run             # Fresh migrate + seed

# Cache
php artisan cache:clear            # Clear cache
php artisan config:clear           # Clear config cache
php artisan route:clear            # Clear route cache
php artisan view:clear             # Clear view cache

# Assets
npm run dev                        # Development build
npm run build                      # Production build
```

---

## 🔐 Default Credentials

After seeding, you can create an admin user using the method mentioned in [Database Setup](#-database-setup) section.

**Default Role:** `attendee` (assigned to new registrations)

---

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue 3 Documentation](https://vuejs.org/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Ant Design Vue](https://antdv.com/)

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 👥 Support

For issues and questions, please open an issue on the repository.

---

**Happy Coding! 🚀**
