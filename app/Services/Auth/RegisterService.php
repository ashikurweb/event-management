<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
            ]);

            // Create user profile if needed
            // $user->profile()->create([]);

            // Assign default role if needed
            // $this->assignDefaultRole($user);

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
     * @param User $user
     * @return void
     */
    protected function assignDefaultRole(User $user): void
    {
        // Uncomment if you have roles system
        // $defaultRole = Role::where('name', 'user')->first();
        // if ($defaultRole) {
        //     $user->roles()->attach($defaultRole->id);
        // }
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
        // Uncomment when email verification is implemented
        // try {
        //     $user->sendEmailVerificationNotification();
        // } catch (\Exception $e) {
        //     Log::warning('Failed to send email verification', [
        //         'user_id' => $user->id,
        //         'error' => $e->getMessage(),
        //     ]);
        // }
    }
}

