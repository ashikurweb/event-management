<?php

namespace App\Services\Auth;

use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetOtpMail;
use Carbon\Carbon;

/**
 * OTP Service
 * 
 * Handles OTP generation, validation, and management
 * Following Single Responsibility Principle
 */
class OtpService
{
    /**
     * OTP expiration time in minutes (2 minutes 30 seconds = 2.5 minutes)
     */
    const OTP_EXPIRY_MINUTES = 2.5;

    /**
     * OTP length
     */
    const OTP_LENGTH = 6;

    /**
     * Maximum OTP attempts per email per hour
     */
    const MAX_ATTEMPTS_PER_HOUR = 5;

    /**
     * Generate and send OTP for password reset.
     *
     * @param string $email
     * @return array
     * @throws \Exception
     */
    public function generateAndSendOtp(string $email): array
    {
        try {
            // Check if user exists
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                // Don't reveal if email exists or not (security best practice)
                // Return success even if user doesn't exist
                return [
                    'success' => true,
                    'message' => 'If the email exists, an OTP has been sent.',
                ];
            }

            // Check rate limiting
            $this->checkRateLimit($email);

            DB::beginTransaction();

            // Invalidate all previous unused OTPs for this email
            $this->invalidatePreviousOtps($email);

            // Generate new OTP
            $otp = $this->generateOtp();
            $expiresAt = now()->addMinutes(self::OTP_EXPIRY_MINUTES);

            // Create OTP record
            $otpRecord = PasswordResetOtp::create([
                'email' => $email,
                'otp' => $otp,
                'expires_at' => $expiresAt,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            // Send OTP via email
            $this->sendOtpEmail($user, $otp);

            // Log OTP generation
            Log::info('Password reset OTP generated', [
                'email' => $email,
                'user_id' => $user->id,
                'expires_at' => $expiresAt,
                'ip_address' => request()->ip(),
            ]);

            return [
                'success' => true,
                'message' => 'OTP has been sent to your email address.',
                'expires_at' => $expiresAt->toIso8601String(),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('OTP generation failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Verify OTP.
     *
     * @param string $email
     * @param string $otp
     * @return PasswordResetOtp|null
     */
    public function verifyOtp(string $email, string $otp): ?PasswordResetOtp
    {
        $otpRecord = PasswordResetOtp::validForEmail($email)
            ->where('otp', $otp)
            ->first();

        if (!$otpRecord) {
            Log::warning('Invalid OTP attempt', [
                'email' => $email,
                'ip_address' => request()->ip(),
            ]);
            return null;
        }

        // Mark as used
        $otpRecord->markAsUsed();

        Log::info('OTP verified successfully', [
            'email' => $email,
            'otp_id' => $otpRecord->id,
            'ip_address' => request()->ip(),
        ]);

        return $otpRecord;
    }

    /**
     * Generate a random 6-digit OTP.
     *
     * @return string
     */
    protected function generateOtp(): string
    {
        return str_pad((string) random_int(100000, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Invalidate all previous unused OTPs for an email.
     *
     * @param string $email
     * @return void
     */
    protected function invalidatePreviousOtps(string $email): void
    {
        PasswordResetOtp::where('email', $email)
            ->where('is_used', false)
            ->update(['is_used' => true]);
    }

    /**
     * Check rate limiting for OTP requests.
     *
     * @param string $email
     * @return void
     * @throws \Exception
     */
    protected function checkRateLimit(string $email): void
    {
        $oneHourAgo = now()->subHour();
        
        $recentAttempts = PasswordResetOtp::where('email', $email)
            ->where('created_at', '>=', $oneHourAgo)
            ->count();

        if ($recentAttempts >= self::MAX_ATTEMPTS_PER_HOUR) {
            Log::warning('OTP rate limit exceeded', [
                'email' => $email,
                'attempts' => $recentAttempts,
                'ip_address' => request()->ip(),
            ]);

            throw new \Exception('Too many OTP requests. Please try again later.');
        }
    }

    /**
     * Send OTP email to user.
     *
     * @param User $user
     * @param string $otp
     * @return void
     */
    protected function sendOtpEmail(User $user, string $otp): void
    {
        try {
            Mail::to($user->email)->send(new PasswordResetOtpMail($user, $otp));
            
            Log::info('OTP email sent', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            
            // Don't throw exception - OTP is already saved
            // User can request a new one if email fails
        }
    }

    /**
     * Clean up expired OTPs (can be called via scheduled task).
     *
     * @return int Number of deleted records
     */
    public function cleanupExpiredOtps(): int
    {
        $deleted = PasswordResetOtp::expired()
            ->orWhere(function ($query) {
                $query->where('is_used', true)
                    ->where('used_at', '<', now()->subDays(7));
            })
            ->delete();

        Log::info('Expired OTPs cleaned up', [
            'deleted_count' => $deleted,
        ]);

        return $deleted;
    }

    /**
     * Get remaining time for OTP in seconds.
     *
     * @param string $email
     * @return int|null
     */
    public function getRemainingTime(string $email): ?int
    {
        $otpRecord = PasswordResetOtp::validForEmail($email)
            ->latest('created_at')
            ->first();

        if (!$otpRecord) {
            return null;
        }

        $remaining = now()->diffInSeconds($otpRecord->expires_at, false);
        return $remaining > 0 ? $remaining : 0;
    }
}

