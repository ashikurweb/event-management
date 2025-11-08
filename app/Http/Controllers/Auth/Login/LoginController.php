<?php

namespace App\Http\Controllers\Auth\Login;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Login\LoginRequest;
use App\Services\Auth\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    /**
     * Social Auth Service instance.
     *
     * @var SocialAuthService
     */
    protected SocialAuthService $socialAuthService;

    /**
     * Create a new controller instance.
     *
     * @param SocialAuthService $socialAuthService
     */
    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }
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
    public function redirectToProvider(string $provider): RedirectResponse
    {
        // Validate provider
        if (!in_array($provider, SocialAuthService::SUPPORTED_PROVIDERS)) {
            Log::warning('Unsupported social login provider', [
                'provider' => $provider,
                'ip_address' => request()->ip(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Unsupported login provider.');
        }

        try {
            // Request additional scopes for GitHub to get email
            if ($provider === 'github') {
                return Socialite::driver($provider)
                    ->scopes(['user:email'])
                    ->redirect();
            }
            
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            Log::error('Social login redirect failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'ip_address' => request()->ip(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Failed to initiate social login. Please try again.');
        }
    }

    /**
     * Obtain the user information from provider.
     *
     * @param  string  $provider
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback(string $provider, Request $request): RedirectResponse
    {
        // Log callback attempt for debugging
        Log::info('Social login callback received', [
            'provider' => $provider,
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
        ]);

        // Validate provider
        if (!in_array($provider, SocialAuthService::SUPPORTED_PROVIDERS)) {
            Log::warning('Unsupported provider in callback', [
                'provider' => $provider,
                'supported' => SocialAuthService::SUPPORTED_PROVIDERS,
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Unsupported login provider.');
        }

        try {
            // Get user from social provider
            $socialiteUser = Socialite::driver($provider)->user();

            // For GitHub, if email is not available, try to fetch from GitHub API
            if ($provider === 'github' && empty($socialiteUser->getEmail())) {
                try {
                    $token = $socialiteUser->token;
                    if ($token) {
                        $response = \Illuminate\Support\Facades\Http::withHeaders([
                            'Accept' => 'application/vnd.github.v3+json',
                            'Authorization' => 'token ' . $token,
                        ])->get('https://api.github.com/user/emails');
                        
                        if ($response->successful()) {
                            $emails = $response->json();
                            // Find primary email or first verified email
                            foreach ($emails as $email) {
                                if (isset($email['primary']) && $email['primary'] && isset($email['verified']) && $email['verified']) {
                                    $socialiteUser->email = $email['email'];
                                    break;
                                } elseif (isset($email['verified']) && $email['verified'] && empty($socialiteUser->getEmail())) {
                                    $socialiteUser->email = $email['email'];
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch GitHub email from API', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Log socialite user data for debugging
            Log::info('Socialite user data', [
                'provider' => $provider,
                'id' => $socialiteUser->getId(),
                'email' => $socialiteUser->getEmail(),
                'name' => $socialiteUser->getName(),
                'nickname' => $socialiteUser->getNickname(),
                'token' => $socialiteUser->token ? 'present' : 'missing',
                'refreshToken' => $socialiteUser->refreshToken ? 'present' : 'missing',
                'expiresIn' => $socialiteUser->expiresIn ?? 'not set',
                'user_data_keys' => array_keys($socialiteUser->user ?? []),
            ]);

            // Check if email is available (required for user creation)
            if (empty($socialiteUser->getEmail())) {
                Log::error('Social login failed: No email from provider', [
                    'provider' => $provider,
                    'provider_id' => $socialiteUser->getId(),
                ]);

                return redirect()->route('login')
                    ->with('error', 'Unable to retrieve email from ' . ucfirst($provider) . '. Please ensure your account has a verified email address and grant email permissions.');
            }

            // Find or create user
            // Note: Social login users don't need email verification
            // - New users: email_verified_at is set automatically
            // - Existing users: email is auto-verified when linking social account
            $user = $this->socialAuthService->findOrCreateUser($provider, $socialiteUser);

            // Refresh user model to ensure all attributes are loaded (especially email_verified_at)
            $user->refresh();

            // Login the user
            Auth::login($user, true);
            $request->session()->regenerate();

            // Update last login timestamp
            $user->update(['last_login_at' => now()]);
            
            // Double-check email is verified (should always be true for social login)
            if (!$user->hasVerifiedEmail()) {
                // Fallback: verify email if somehow not verified
                $user->markEmailAsVerified();
                $user->refresh();
                Log::warning('Email was not verified for social login user, auto-verified', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'provider' => $provider,
                ]);
            }

            // Log successful social login
            Log::info('User logged in via social provider', [
                'user_id' => $user->id,
                'email' => $user->email,
                'provider' => $provider,
                'email_verified' => $user->hasVerifiedEmail(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Redirect to intended URL or dashboard
            // No email verification check needed - social providers verify emails
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome! You have been logged in successfully.');

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::warning('Social login state mismatch', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Social login session expired. Please try again.');

        } catch (\Exception $e) {
            Log::error('Social login callback failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Failed to login with social provider. Please try again.');
        }
    }
}

