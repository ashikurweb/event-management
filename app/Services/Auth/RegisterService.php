<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Config;

/**
 * Register Service
 * 
 * Handles user registration business logic
 * Following Single Responsibility Principle
 */
class RegisterService
{
    /**
     * Create a new user account.
     *
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function createUser(array $data): User
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
                // email_verified_at will be null until verified
            ]);

            // Assign default role (attendee)
            $this->assignDefaultRole($user);

            DB::commit();

            // Log successful registration
            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => request()->ip(),
            ]);

            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('User registration failed', [
                'email' => $data['email'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Assign default role to user.
     * 
     * Uses configuration from config/roles.php for flexibility.
     * For event management systems, 'attendee' is the industry standard.
     *
     * @param User $user
     * @return void
     */
    protected function assignDefaultRole(User $user): void
    {
        try {
            $defaultRole = Config::get('roles.default_role', 'attendee');
            
            $user->assignRole($defaultRole);
            
            Log::info('Default role assigned to user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $defaultRole,
            ]);
        } catch (\Exception $e) {
            Log::warning('Default role assignment failed. Please ensure RolePermissionSeeder has been run.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'default_role' => Config::get('roles.default_role', 'attendee'),
                'error' => $e->getMessage(),
            ]);
            
            // Don't throw exception - allow registration to complete
            // Admin can manually assign role later
        }
    }

    /**
     * Send welcome email to new user.
     *
     * @param User $user
     * @return void
     */
    public function sendWelcomeEmail(User $user): void
    {
        // Uncomment when email system is ready
        // try {
        //     Mail::to($user->email)->send(new WelcomeEmail($user));
        // } catch (\Exception $e) {
        //     Log::warning('Failed to send welcome email', [
        //         'user_id' => $user->id,
        //         'error' => $e->getMessage(),
        //     ]);
        // }
    }

    /**
     * Send email verification notification.
     *
     * @param User $user
     * @return void
     */
    public function sendEmailVerification(User $user): void
    {
        try {
            $user->notify(new VerifyEmailNotification());
            Log::info('Email verification sent', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to send email verification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw exception, just log it
        }
    }
}

