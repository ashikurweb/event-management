<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\SocialAccount;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Illuminate\Support\Str;

/**
 * Social Auth Service
 * 
 * Handles social authentication business logic
 * Following Single Responsibility Principle
 */
class SocialAuthService
{
    /**
     * Supported providers.
     */
    const SUPPORTED_PROVIDERS = ['google', 'facebook', 'github'];

    /**
     * Find or create user from social provider.
     *
     * @param string $provider
     * @param SocialiteUser $socialiteUser
     * @return User
     * @throws \Exception
     */
    public function findOrCreateUser(string $provider, SocialiteUser $socialiteUser): User
    {
        if (!in_array($provider, self::SUPPORTED_PROVIDERS)) {
            throw new \InvalidArgumentException("Unsupported provider: {$provider}");
        }

        try {
            DB::beginTransaction();

            // Check if social account exists
            $socialAccount = SocialAccount::where('provider', $provider)
                ->where('provider_id', $socialiteUser->getId())
                ->first();

            if ($socialAccount) {
                // Update social account data
                $this->updateSocialAccount($socialAccount, $socialiteUser);
                $user = $socialAccount->user;
                
                // Update user avatar from social account if user doesn't have one or if social avatar is newer
                $socialAvatar = $socialiteUser->getAvatar();
                if ($socialAvatar && (empty($user->avatar) || $socialAccount->avatar !== $user->avatar)) {
                    $user->update(['avatar' => $socialAvatar]);
                    $user->refresh();
                }
            } else {
                // Check if user exists by email (email is required)
                $email = $socialiteUser->getEmail();
                
                if (empty($email)) {
                    throw new \Exception('Email is required but not provided by the social provider.');
                }
                
                $user = User::where('email', $email)->first();

                if ($user) {
                    // Link social account to existing user
                    $socialAccount = $this->createSocialAccount($user, $provider, $socialiteUser);
                    
                    // Update user avatar from social account if user doesn't have one
                    $socialAvatar = $socialiteUser->getAvatar();
                    if ($socialAvatar && empty($user->avatar)) {
                        $user->update(['avatar' => $socialAvatar]);
                        $user->refresh();
                    }
                    
                    // Verify email if not already verified (social providers verify emails)
                    if (!$user->hasVerifiedEmail()) {
                        $user->markEmailAsVerified();
                        // Refresh user model to ensure email_verified_at is loaded
                        $user->refresh();
                        Log::info('Email auto-verified via social login', [
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'provider' => $provider,
                            'email_verified_at' => $user->email_verified_at,
                        ]);
                    }
                } else {
                    // Create new user and social account
                    $user = $this->createUserFromSocial($provider, $socialiteUser);
                }
            }

            DB::commit();

            // Log successful social login
            Log::info('Social login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'provider' => $provider,
                'provider_id' => $socialiteUser->getId(),
            ]);

            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Social login failed', [
                'provider' => $provider,
                'email' => $socialiteUser->getEmail(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Create user from social provider data.
     *
     * @param string $provider
     * @param SocialiteUser $socialiteUser
     * @return User
     */
    protected function createUserFromSocial(string $provider, SocialiteUser $socialiteUser): User
    {
        // Validate email is present
        $email = $socialiteUser->getEmail();
        if (empty($email)) {
            throw new \Exception('Cannot create user: Email is required but not provided by ' . $provider);
        }

        // Extract name from social user
        $name = $this->extractName($socialiteUser);
        $firstName = $name['first_name'];
        $lastName = $name['last_name'];

        // Create user with email verified (social providers verify emails)
        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make(Str::random(32)), // Random password for social users
            'avatar' => $socialiteUser->getAvatar(),
            'status' => 'active',
            'email_verified_at' => now(), // Social providers verify emails
        ]);
        
        // Ensure email_verified_at is set (double check)
        if (empty($user->email_verified_at)) {
            $user->markEmailAsVerified();
        }
        
        // Refresh to ensure all attributes are loaded
        $user->refresh();

        // Assign default role
        $this->assignDefaultRole($user);

        // Create social account
        $this->createSocialAccount($user, $provider, $socialiteUser);

        Log::info('User created from social login', [
            'user_id' => $user->id,
            'email' => $user->email,
            'provider' => $provider,
        ]);

        return $user;
    }

    /**
     * Create social account for user.
     *
     * @param User $user
     * @param string $provider
     * @param SocialiteUser $socialiteUser
     * @return SocialAccount
     */
    protected function createSocialAccount(User $user, string $provider, SocialiteUser $socialiteUser): SocialAccount
    {
        $token = $socialiteUser->token ?? null;
        $refreshToken = $socialiteUser->refreshToken ?? null;
        
        // Handle token expiration based on provider
        $expiresAt = $this->calculateTokenExpiration($provider, $socialiteUser);

        return SocialAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $socialiteUser->getId(),
            'email' => $socialiteUser->getEmail(),
            'avatar' => $socialiteUser->getAvatar(),
            'token' => $token,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt,
            'provider_data' => $socialiteUser->user ?? null,
        ]);
    }

    /**
     * Update social account data.
     *
     * @param SocialAccount $socialAccount
     * @param SocialiteUser $socialiteUser
     * @return void
     */
    protected function updateSocialAccount(SocialAccount $socialAccount, SocialiteUser $socialiteUser): void
    {
        $token = $socialiteUser->token ?? null;
        $refreshToken = $socialiteUser->refreshToken ?? null;
        
        // Handle token expiration based on provider
        $expiresAt = $this->calculateTokenExpiration($socialAccount->provider, $socialiteUser);

        $socialAccount->update([
            'email' => $socialiteUser->getEmail(),
            'avatar' => $socialiteUser->getAvatar(),
            'token' => $token,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt,
            'provider_data' => $socialiteUser->user ?? null,
        ]);
    }

    /**
     * Calculate token expiration based on provider.
     *
     * @param string $provider
     * @param SocialiteUser $socialiteUser
     * @return \Carbon\Carbon|null
     */
    protected function calculateTokenExpiration(string $provider, SocialiteUser $socialiteUser): ?\Carbon\Carbon
    {
        // Check if expiresIn is directly available
        if (isset($socialiteUser->expiresIn) && $socialiteUser->expiresIn) {
            return now()->addSeconds($socialiteUser->expiresIn);
        }

        // Provider-specific handling
        switch ($provider) {
            case 'github':
                // GitHub OAuth tokens typically don't expire (unless revoked)
                // But we can set a default expiration (e.g., 1 year) for safety
                // Or check if token has expiration in provider_data
                if (isset($socialiteUser->user['expires_at'])) {
                    return \Carbon\Carbon::parse($socialiteUser->user['expires_at']);
                }
                // GitHub tokens are usually long-lived, set to 1 year from now
                return now()->addYear();
                
            case 'google':
                // Google tokens usually have expiresIn
                // Default to 1 hour if not specified
                return now()->addHour();
                
            case 'facebook':
                // Facebook tokens usually have expiresIn
                // Default to 2 hours if not specified
                return now()->addHours(2);
                
            default:
                // Default expiration: 1 hour
                return now()->addHour();
        }
    }

    /**
     * Extract first and last name from social user.
     *
     * @param SocialiteUser $socialiteUser
     * @return array{first_name: string, last_name: string}
     */
    protected function extractName(SocialiteUser $socialiteUser): array
    {
        $name = $socialiteUser->getName();
        
        if (empty($name)) {
            // Fallback to email username
            $email = $socialiteUser->getEmail();
            $username = explode('@', $email)[0];
            return [
                'first_name' => $username,
                'last_name' => '',
            ];
        }

        $nameParts = explode(' ', $name, 2);
        
        return [
            'first_name' => $nameParts[0] ?? 'User',
            'last_name' => $nameParts[1] ?? '',
        ];
    }

    /**
     * Assign default role to user.
     *
     * @param User $user
     * @return void
     */
    protected function assignDefaultRole(User $user): void
    {
        try {
            $defaultRole = Config::get('roles.default_role', 'attendee');
            $user->assignRole($defaultRole);
            
            Log::info('Default role assigned to social user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $defaultRole,
            ]);
        } catch (\Exception $e) {
            Log::warning('Default role assignment failed for social user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

