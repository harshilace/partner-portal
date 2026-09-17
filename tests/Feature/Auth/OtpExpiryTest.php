<?php

namespace Tests\Feature\Auth;

use App\Domain\Authentication\Support\OtpToken;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OtpExpiryTest extends TestCase
{
    public function test_generates_six_digit_numeric_code_by_default(): void
    {
        $code = OtpToken::generateCode();

        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $code);
    }

    public function test_generates_custom_length_numeric_code(): void
    {
        $code = OtpToken::generateCode(8);

        $this->assertMatchesRegularExpression('/^[0-9]{8}$/', $code);
    }

    public function test_hashes_and_verifies_otp_token(): void
    {
        $code = '482910';
        $hashed = OtpToken::hash($code);

        $this->assertNotEquals($code, $hashed);
        $this->assertTrue(OtpToken::verify($code, $hashed));
        $this->assertFalse(OtpToken::verify('000000', $hashed));
    }

    public function test_detects_active_unexpired_otp(): void
    {
        $generatedAt = Carbon::now()->subMinutes(5);

        $this->assertFalse(OtpToken::isExpired($generatedAt, lifetimeMinutes: 10));
    }

    public function test_detects_expired_otp(): void
    {
        $generatedAt = Carbon::now()->subMinutes(11);

        $this->assertTrue(OtpToken::isExpired($generatedAt, lifetimeMinutes: 10));
    }

    public function test_detects_exhausted_attempts(): void
    {
        $this->assertFalse(OtpToken::isExhausted(2, maxAttempts: 3));
        $this->assertTrue(OtpToken::isExhausted(3, maxAttempts: 3));
        $this->assertTrue(OtpToken::isExhausted(5, maxAttempts: 3));
    }
}
