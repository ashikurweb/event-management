<?php

namespace App\Http\Controllers\Auth\Login;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Login\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    /**
     * Show the login form.
     *
     * @return Response
     */
    public function showLoginForm(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param LoginRequest $request
     * @return RedirectResponse
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];
        $remember = $request->boolean('remember', false);

        // Attempt to authenticate the user
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Update last login timestamp
            $user->update(['last_login_at' => now()]);

            // Log successful login
            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'remember_me' => $remember,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                return redirect()
                    ->route('verification.notice')
                    ->with('info', 'Please verify your email address to access the dashboard.');
            }

            // Redirect to intended URL or dashboard
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back! You have been logged in successfully.');
        }

        // Log failed login attempt
        Log::warning('Failed login attempt', [
            'email' => $credentials['email'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $errorMessage = 'The provided credentials do not match our records.';
        
        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => $errorMessage,
            ])
            ->with('error', $errorMessage);
    }

    /**
     * Log the user out of the application.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Log logout
        if ($user) {
            Log::info('User logged out', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Redirect the user to the provider authentication page.
     *
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        // Backend logic will be implemented later
        // Social login redirect logic will go here
        
        return redirect()->route('login')->with('error', 'Social login not implemented yet.');
    }

    /**
     * Obtain the user information from provider.
     *
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback($provider)
    {
        // Backend logic will be implemented later
        // Social login callback logic will go here
        
        return redirect()->route('login')->with('error', 'Social login not implemented yet.');
    }
}

