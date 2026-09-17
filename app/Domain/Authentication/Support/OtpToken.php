<?php

namespace App\Domain\Authentication\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class OtpToken
{
    /**
     * Generate a cryptographically secure numeric OTP code.
     */
    public static function generateCode(int $digits = 6): string
    {
        $min = (int) (10 ** ($digits - 1));
        $max = (int) ((10 ** $digits) - 1);

        return (string) random_int($min, $max);
    }

    /**
     * Compute a secure hash of the OTP code.
     */
    public static function hash(string $plainToken): string
    {
        return Hash::make($plainToken);
    }

    /**
     * Verify whether a candidate OTP matches the stored hash.
     */
    public static function verify(string $candidateToken, string $hashedToken): bool
    {
        return Hash::check($candidateToken, $hashedToken);
    }

    /**
     * Check if the OTP has expired based on its generation timestamp and lifetime.
     */
    public static function isExpired(CarbonInterface|string $generatedAt, int $lifetimeMinutes = 10): bool
    {
        $timestamp = is_string($generatedAt) ? Carbon::parse($generatedAt) : $generatedAt;

        return $timestamp->addMinutes($lifetimeMinutes)->isPast();
    }

    /**
     * Check if allowed verification attempts have been exhausted.
     */
    public static function isExhausted(int $attempts, int $maxAttempts = 3): bool
    {
        return $attempts >= $maxAttempts;
    }
}
