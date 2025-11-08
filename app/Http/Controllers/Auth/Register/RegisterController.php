<?php

namespace App\Http\Controllers\Auth\Register;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Register\RegisterRequest;
use App\Services\Auth\RegisterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    /**
     * Register service instance.
     *
     * @var RegisterService
     */
    protected RegisterService $registerService;

    /**
     * Create a new controller instance.
     *
     * @param RegisterService $registerService
     */
    public function __construct(RegisterService $registerService)
    {
        $this->registerService = $registerService;
    }

    /**
     * Show the registration form.
     *
     * @return Response
     */
    public function showRegisterForm(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param RegisterRequest $request
     * @return RedirectResponse
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            // Create user using service
            $user = $this->registerService->createUser($validated);

            // Send welcome email (async if queue is configured)
            $this->registerService->sendWelcomeEmail($user);

            // Send email verification notification
            $this->registerService->sendEmailVerification($user);

            // Log in the user (but they need to verify email to access dashboard)
            Auth::login($user);
            $request->session()->regenerate();

            // Log successful registration
            Log::info('User registered and logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Redirect to email verification notice page
            return redirect()
                ->route('verification.notice')
                ->with('success', 'Registration successful! Please verify your email address to continue.');

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors
            Log::error('Database error during registration', [
                'error' => $e->getMessage(),
                'email' => $request->input('email'),
            ]);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'email' => 'Registration failed due to a system error. Please try again later.',
                ]);

        } catch (\Exception $e) {
            // Handle any other exceptions
            Log::error('Unexpected error during registration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->input('email'),
            ]);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'email' => 'An unexpected error occurred. Please try again later.',
                ]);
        }
    }
}

