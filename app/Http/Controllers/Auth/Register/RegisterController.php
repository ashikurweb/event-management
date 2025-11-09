<?php

namespace App\Http\Controllers\Auth\Register;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Register\RegisterRequest;
use App\Services\Auth\RegisterService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
            $user = $this->registerService->createUser($request->validated());
            
            $this->registerService->sendWelcomeEmail($user);
            $this->registerService->sendEmailVerification($user);
            
            Auth::login($user);
            $request->session()->regenerate();
            
            return redirect()
                ->route('verification.notice')
                ->with('success', 'Registration successful! Please verify your email address to continue.');
        } catch (Exception) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'email' => 'Registration failed. Please try again later.',
                ]);
        }
    }
}

