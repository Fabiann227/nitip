<?php

namespace App\Services;

use App\Exceptions\NitipException;
use App\Models\User;
use App\Notifications\VerifyEmailOtp;
use App\Support\Codes;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * OTP state lives on the users table (otp_* columns).
 */
class EmailVerificationService
{
    public function send(User $user): void
    {
        $length = (int) config('nitip.otp.length', 6);
        $ttl = (int) config('nitip.otp.ttl_minutes', 10);
        $code = Codes::otp($length);

        $user->forceFill([
            'otp_code_hash' => Hash::make($code),
            'otp_expires_at' => now()->addMinutes($ttl),
            'otp_attempts' => 0,
            'otp_sent_at' => now(),
        ])->save();

        $this->rememberDevCode($user, $code);

        $user->notify(new VerifyEmailOtp($code, $ttl));
    }

    public function resend(User $user): void
    {
        $wait = $this->secondsUntilResend($user);

        if ($wait > 0) {
            throw new NitipException("Tunggu {$wait} detik sebelum mengirim ulang kode.");
        }

        $this->send($user);
    }

    /**
     * Verify the submitted code. Returns true on success and marks the email as verified.
     */
    public function verify(User $user, string $code): bool
    {
        if ($this->bypassEnabled()) {
            $this->markVerified($user);

            return true;
        }

        if (! $user->otp_code_hash) {
            throw new NitipException('Kode verifikasi belum dikirim. Silakan kirim ulang kode.');
        }

        if ($user->otp_expires_at === null || $user->otp_expires_at->isPast()) {
            throw new NitipException('Kode verifikasi sudah kedaluwarsa. Silakan kirim ulang kode.');
        }

        if ($user->otp_attempts >= (int) config('nitip.otp.max_attempts', 5)) {
            throw new NitipException('Terlalu banyak percobaan. Silakan kirim ulang kode baru.');
        }

        if (! Hash::check($code, $user->otp_code_hash)) {
            $user->increment('otp_attempts');

            return false;
        }

        $this->markVerified($user);

        return true;
    }

    public function hasPendingCode(User $user): bool
    {
        return $user->otp_code_hash !== null;
    }

    public function secondsUntilResend(User $user): int
    {
        if (! $user->otp_sent_at) {
            return 0;
        }

        $cooldown = (int) config('nitip.otp.resend_cooldown_seconds', 60);
        $elapsed = (int) $user->otp_sent_at->diffInSeconds(now(), false);

        return max(0, $cooldown - $elapsed);
    }

    /**
     * Dummy mode: any code is accepted. Only possible outside production.
     */
    public function bypassEnabled(): bool
    {
        return app()->environment('local', 'testing') && (bool) config('nitip.otp.bypass', false);
    }

    /**
     * In local development we may show the OTP on screen so the flow can be tested without a mailbox.
     */
    public function devCode(User $user): ?string
    {
        if (! $this->devModeEnabled()) {
            return null;
        }

        return Cache::get($this->devCacheKey($user));
    }

    private function markVerified(User $user): void
    {
        $user->forceFill([
            'email_verified_at' => now(),
            'otp_code_hash' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
        ])->save();

        Cache::forget($this->devCacheKey($user));

        event(new Verified($user));
    }

    private function rememberDevCode(User $user, string $code): void
    {
        if ($this->devModeEnabled()) {
            Cache::put($this->devCacheKey($user), $code, now()->addMinutes((int) config('nitip.otp.ttl_minutes', 10)));
        }
    }

    private function devModeEnabled(): bool
    {
        return app()->environment('local', 'testing') && (bool) config('nitip.otp.dev_show', false);
    }

    private function devCacheKey(User $user): string
    {
        return "nitip:otp:dev:{$user->id}";
    }
}
