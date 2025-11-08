# 📧 Mailtrap Configuration Guide

## 🔧 .env Configuration

আপনার `.env` file-এ এই settings add করুন:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@eventhub.com
MAIL_FROM_NAME="EventHub"
```

## 📋 Mailtrap Setup Steps

### Step 1: Mailtrap Account
1. [Mailtrap.io](https://mailtrap.io) এ sign up করুন
2. Free plan-এ 500 emails/month পাবেন

### Step 2: Get Credentials
1. Mailtrap dashboard → **Email Testing** → **Inboxes**
2. আপনার inbox select করুন
3. **SMTP Settings** → **Show Credentials**
4. Username এবং Password copy করুন

### Step 3: Update .env
```env
MAIL_USERNAME=your-actual-mailtrap-username
MAIL_PASSWORD=your-actual-mailtrap-password
```

### Step 4: Clear Config Cache
```bash
php artisan config:clear
```

## ✅ Test Email

### Option 1: Tinker
```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Test email from EventHub', function ($message) {
    $message->to('test@example.com')
            ->subject('Test Email');
});
```

### Option 2: Register Test
1. Register করুন একটি test account দিয়ে
2. Mailtrap inbox-এ email check করুন

## 🔍 Troubleshooting

### Email যাচ্ছে না?

#### 1. Check .env
```bash
php artisan tinker
```
```php
config('mail.mailers.smtp.host');
config('mail.mailers.smtp.port');
config('mail.mailers.smtp.username');
// Password check করবেন না (security)
```

#### 2. Check Logs
```bash
tail -f storage/logs/laravel.log
```

#### 3. Test Connection
```bash
php artisan tinker
```
```php
use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('Test', function ($m) {
        $m->to('test@example.com')->subject('Test');
    });
    echo "Email sent!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

#### 4. Check Queue (if using queue)
```bash
# Check if jobs are queued
php artisan queue:work

# Or check jobs table
php artisan tinker
```
```php
DB::table('jobs')->count();
```

## 🚀 Quick Fix Applied

আমি `VerifyEmailNotification` থেকে `ShouldQueue` remove করে দিয়েছি। এখন email **directly send** হবে, queue-এর দরকার নেই।

## 📝 Alternative: Use Queue (For Production)

যদি queue ব্যবহার করতে চান:

### Step 1: Re-enable Queue
`app/Notifications/VerifyEmailNotification.php`:
```php
class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;
```

### Step 2: Run Queue Worker
```bash
php artisan queue:work
```

### Step 3: Or Use Supervisor (Production)
Supervisor config file create করুন queue worker-এর জন্য।

## 🎯 Current Status

✅ **Direct Email Sending** - Queue ছাড়াই email send হবে
✅ **Mailtrap Ready** - `.env` configure করলেই কাজ করবে

---

**Important:** `.env` file update করার পর **must** run করুন:
```bash
php artisan config:clear
```

