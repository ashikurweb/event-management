<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page.
     */
    public function index()
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            abort(401);
        }
        
        $user->load(['profile', 'socialAccounts']);

        // Check if user can change password
        // Logic: If user has no social accounts, they registered - can change password
        // If user has social accounts, check if oldest social account was created at the same time as user (within 1 minute)
        // This indicates social login, so don't allow password change
        $canChangePassword = $this->canUserChangePassword($user);

        return Inertia::render('Dashboard/Profile', [
            'user' => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'gender' => $user->gender,
                'bio' => $user->bio,
                'status' => $user->status,
                'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
                'last_login_at' => $user->last_login_at?->format('Y-m-d H:i:s'),
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                'can_change_password' => $canChangePassword,
                'profile' => $user->profile ? [
                    'company_name' => $user->profile->company_name,
                    'website' => $user->profile->website,
                    'address_line1' => $user->profile->address_line1,
                    'address_line2' => $user->profile->address_line2,
                    'city' => $user->profile->city,
                    'state' => $user->profile->state,
                    'country' => $user->profile->country,
                    'postal_code' => $user->profile->postal_code,
                    'timezone' => $user->profile->timezone,
                    'language' => $user->profile->language,
                    'preferences' => $user->profile->preferences,
                    'full_address' => $user->profile->full_address,
                ] : null,
                'social_accounts' => $user->socialAccounts->map(function ($account) {
                    return [
                        'id' => $account->id,
                        'provider' => $account->provider,
                        'email' => $account->email,
                        'avatar' => $account->avatar,
                        'created_at' => $account->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(ProfileUpdateRequest $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            abort(401);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        // Update user fields
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->date_of_birth = $request->date_of_birth;
        $user->gender = $request->gender;
        $user->bio = $request->bio;
        $user->save();

        // Update or create user profile
        $profile = $user->profile;
        if (!$profile) {
            $profile = new UserProfile();
            $profile->user_id = $user->id;
        }

        $profile->company_name = $request->company_name;
        $profile->website = $request->website;
        $profile->address_line1 = $request->address_line1;
        $profile->address_line2 = $request->address_line2;
        $profile->city = $request->city;
        $profile->state = $request->state;
        $profile->country = $request->country;
        $profile->postal_code = $request->postal_code;
        $profile->timezone = $request->timezone ?? 'UTC';
        $profile->language = $request->language ?? 'en';
        $profile->preferences = $request->preferences;
        $profile->save();

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully!');
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(Request $request)
    {
        try {
            $validated = $request->validate([
                'avatar' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            ]);

            /** @var User|null $user */
            $user = Auth::user();
            
            if (!$user) {
                // For Inertia requests, redirect with error
                if ($request->header('X-Inertia')) {
                    return redirect()->route('profile.index')
                        ->with('error', 'Unauthorized');
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
            $user->save();

            // For Inertia requests, redirect back with success
            if ($request->header('X-Inertia')) {
                return redirect()->route('profile.index')
                    ->with('success', 'Avatar updated successfully!');
            }

            // For API/JSON requests (fallback)
            return response()->json([
                'success' => true,
                'avatar' => Storage::url($avatarPath),
                'message' => 'Avatar updated successfully!',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // For Inertia requests, let validation exception bubble up
            if ($request->header('X-Inertia')) {
                throw $e;
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // For Inertia requests, redirect with error
            if ($request->header('X-Inertia')) {
                return redirect()->route('profile.index')
                    ->with('error', $e->getMessage() ?: 'Failed to upload avatar');
            }
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to upload avatar',
            ], 500);
        }
    }

    /**
     * Check if user can change password.
     * Users who registered (not social login) can change password.
     * Users who logged in via social login cannot change password.
     *
     * @param User $user
     * @return bool
     */
    protected function canUserChangePassword(User $user): bool
    {
        // If user has no social accounts, they definitely registered - can change password
        if ($user->socialAccounts->isEmpty()) {
            return true;
        }

        // If user has social accounts, check if oldest social account was created
        // at the same time as user (within 1 minute) - indicates social login
        $oldestSocialAccount = $user->socialAccounts->sortBy('created_at')->first();
        
        if ($oldestSocialAccount) {
            $userCreatedAt = $user->created_at;
            $socialAccountCreatedAt = $oldestSocialAccount->created_at;
            
            // If social account was created within 1 minute of user creation,
            // it's likely a social login user - cannot change password
            $timeDifference = abs($userCreatedAt->diffInSeconds($socialAccountCreatedAt));
            
            if ($timeDifference <= 60) {
                // Social login user - cannot change password
                return false;
            }
        }

        // User registered first, then linked social accounts - can change password
        return true;
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            abort(401);
        }

        // Load social accounts to check if user can change password
        $user->load('socialAccounts');

        // Check if user can change password
        if (!$this->canUserChangePassword($user)) {
            return back()
                ->withInput()
                ->with('error', 'Password change is not available for social login accounts.');
        }

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withInput()
                ->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.index')
            ->with('success', 'Password updated successfully!');
    }
}

