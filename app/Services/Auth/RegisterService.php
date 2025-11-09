<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Register Service
 *
 * Handles user registration business logic.
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
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
            ]);

            $this->assignDefaultRole($user);

            return $user;
        });
    }

    /**
     * Assign default role to user.
     *
     * @param User $user
     * @return void
     */
    protected function assignDefaultRole(User $user): void
    {
        $defaultRole = Config::get('roles.default_role', 'attendee');
        $user->assignRole($defaultRole);
    }

    /**
     * Send email verification notification.
     *
     * @param User $user
     * @return void
     */
    public function sendEmailVerification(User $user): void
    {
        $user->notify(new VerifyEmailNotification());
    }
}

