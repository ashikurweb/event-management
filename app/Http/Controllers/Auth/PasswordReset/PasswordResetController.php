<?php

namespace App\Http\Controllers\Auth\PasswordReset;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordReset\ForgotPasswordRequest;
use App\Http\Requests\Auth\PasswordReset\ResetPasswordRequest;
use App\Services\Auth\OtpService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetController extends Controller
{
    /**
     * OTP Service instance.
     *
     * @var OtpService
     */
    protected OtpService $otpService;

    /**
     * Create a new controller instance.
     *
     * @param OtpService $otpService
     */
    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show the forgot password form.
     *
     * @return Response
     */
    public function showForgotPasswordForm(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Handle forgot password request and send OTP.
     *
     * @param ForgotPasswordRequest $request
     * @return RedirectResponse
     */
    public function sendOtp(ForgotPasswordRequest $request): RedirectResponse
    {
        try {
            $email = $request->validated()['email'];
            
            $result = $this->otpService->generateAndSendOtp($email);

            return back()->with('success', $result['message']);

        } catch (\Exception $e) {
            Log::error('Failed to send password reset OTP', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'ip_address' => $request->ip(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Failed to send OTP. Please try again.');
        }
    }

    /**
     * Show the reset password form.
     *
     * @param Request $request
     * @return Response
     */
    public function showResetPasswordForm(Request $request): Response
    {
        $email = $request->query('email');
        
        return Inertia::render('Auth/ResetPassword', [
            'email' => $email,
        ]);
    }

    /**
     * Handle password reset with OTP verification.
     *
     * @param ResetPasswordRequest $request
     * @return RedirectResponse
     */
    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $email = $validated['email'];
            $otp = $validated['otp'];
            $password = $validated['password'];

            // Verify OTP
            $otpRecord = $this->otpService->verifyOtp($email, $otp);

            if (!$otpRecord) {
                return back()
                    ->withInput()
                    ->with('error', 'Invalid or expired OTP. Please request a new one.');
            }

            // Find user
            $user = User::where('email', $email)->first();

            if (!$user) {
                return back()
                    ->withInput()
                    ->with('error', 'User not found.');
            }

            // Update password
            $user->update([
                'password' => Hash::make($password),
            ]);

            // Log password reset
            Log::info('Password reset successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
            ]);

            return redirect()
                ->route('login')
                ->with('success', 'Your password has been reset successfully. Please login with your new password.');

        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'ip_address' => $request->ip(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to reset password. Please try again.');
        }
    }

    /**
     * Get remaining OTP time (for frontend countdown).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRemainingTime(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $remaining = $this->otpService->getRemainingTime($request->input('email'));

        return response()->json([
            'remaining' => $remaining,
            'expired' => $remaining === null || $remaining <= 0,
        ]);
    }
}
