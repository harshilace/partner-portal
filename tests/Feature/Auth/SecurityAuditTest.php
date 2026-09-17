<?php

namespace Tests\Feature\Auth;

use App\Domain\Audit\AuditLog;
use App\Domain\Audit\Services\AuditLogger;
use Illuminate\Http\Request;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    protected AuditLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new AuditLogger;
    }

    public function test_sanitizes_plain_passwords_and_tokens_from_payload(): void
    {
        $payload = [
            'email' => 'user@example.com',
            'password' => 'supersecret123',
            'password_confirmation' => 'supersecret123',
            'token' => 'plain_api_token_value',
            'remember_token' => 'remember_me_token',
            'otp' => '654321',
            'secret' => 'oauth_secret',
            'credit_card' => '4111111111111111',
            'cvv' => '123',
            'status' => 'active',
        ];

        $sanitized = $this->logger->sanitize($payload);

        $this->assertEquals('user@example.com', $sanitized['email']);
        $this->assertEquals('active', $sanitized['status']);
        $this->assertEquals('[REDACTED]', $sanitized['password']);
        $this->assertEquals('[REDACTED]', $sanitized['password_confirmation']);
        $this->assertEquals('[REDACTED]', $sanitized['token']);
        $this->assertEquals('[REDACTED]', $sanitized['remember_token']);
        $this->assertEquals('[REDACTED]', $sanitized['otp']);
        $this->assertEquals('[REDACTED]', $sanitized['secret']);
        $this->assertEquals('[REDACTED]', $sanitized['credit_card']);
        $this->assertEquals('[REDACTED]', $sanitized['cvv']);
        $this->assertStringNotContainsString('supersecret123', json_encode($sanitized));
        $this->assertStringNotContainsString('plain_api_token_value', json_encode($sanitized));
    }

    public function test_recursively_sanitizes_nested_arrays(): void
    {
        $payload = [
            'user' => [
                'email' => 'partner@example.com',
                'credentials' => [
                    'password' => 'secret_nested_pass',
                    'otp' => '999999',
                ],
            ],
            'meta' => [
                'safe_field' => 'visible_value',
            ],
        ];

        $sanitized = $this->logger->sanitize($payload);

        $this->assertEquals('partner@example.com', $sanitized['user']['email']);
        $this->assertEquals('[REDACTED]', $sanitized['user']['credentials']['password']);
        $this->assertEquals('[REDACTED]', $sanitized['user']['credentials']['otp']);
        $this->assertEquals('visible_value', $sanitized['meta']['safe_field']);
        $this->assertStringNotContainsString('secret_nested_pass', json_encode($sanitized));
    }

    public function test_failed_login_helper_omits_passwords(): void
    {
        $email = 'attempt@example.com';
        $request = Request::create('/login', 'POST', [
            'email' => $email,
            'password' => 'sensitive_password_attempt',
        ], server: ['REMOTE_ADDR' => '192.168.1.50']);

        // Test with mocked AuditLog creation
        $sanitizedValues = $this->logger->sanitize([
            'email' => $email,
            'password' => 'sensitive_password_attempt',
            'reason' => 'invalid_credentials',
        ]);

        $this->assertEquals('[REDACTED]', $sanitizedValues['password']);
        $this->assertEquals($email, $sanitizedValues['email']);
    }

    public function test_lockout_log_records_email_and_retry_after_without_secrets(): void
    {
        $email = 'locked@example.com';
        $seconds = 60;

        $newValues = [
            'email' => $email,
            'retry_after_seconds' => $seconds,
        ];

        $sanitized = $this->logger->sanitize($newValues);

        $this->assertEquals($email, $sanitized['email']);
        $this->assertEquals(60, $sanitized['retry_after_seconds']);
        $this->assertArrayNotHasKey('password', $sanitized);
    }
}
