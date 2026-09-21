<?php

namespace Tests\Feature\Hardening;

use App\Domain\Audit\AuditLog;
use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class SensitiveDataAuditHardeningTest extends TestCase
{
    protected AuditLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new AuditLogger;
    }

    /**
     * Master-mandated sensitive keys are strictly redacted by AuditLogger::sanitize.
     */
    public function test_master_mandated_sensitive_keys_are_redacted(): void
    {
        $payload = [
            'password' => 'plaintext_password_123',
            'token' => 'bearer_token_xyz',
            'otp' => '123456',
            'secret' => 'super_secret_string',
            'credit_card' => '4111111111111234',
            'cvv' => '999',
            // Non-sensitive fields should be preserved
            'email' => 'user@example.com',
            'status' => 'active',
            'amount' => '150.00',
        ];

        $sanitized = $this->logger->sanitize($payload);

        $this->assertEquals('[REDACTED]', $sanitized['password']);
        $this->assertEquals('[REDACTED]', $sanitized['token']);
        $this->assertEquals('[REDACTED]', $sanitized['otp']);
        $this->assertEquals('[REDACTED]', $sanitized['secret']);
        $this->assertEquals('[REDACTED]', $sanitized['credit_card']);
        $this->assertEquals('[REDACTED]', $sanitized['cvv']);

        // Non-sensitive fields preserved
        $this->assertEquals('user@example.com', $sanitized['email']);
        $this->assertEquals('active', $sanitized['status']);
        $this->assertEquals('150.00', $sanitized['amount']);
    }

    /**
     * Existing implementation keys are redacted as established existing behavior.
     */
    public function test_existing_implementation_sensitive_keys_are_redacted(): void
    {
        $payload = [
            'password_confirmation' => 'plaintext_password_123',
            'remember_token' => 'remember_me_token',
            'code' => 'secret_verification_code',
            'authorization' => 'Bearer eyJhbGciOi...',
            'api_key' => 'api_key_secret_abc',
            'role' => 'admin',
        ];

        $sanitized = $this->logger->sanitize($payload);

        $this->assertEquals('[REDACTED]', $sanitized['password_confirmation']);
        $this->assertEquals('[REDACTED]', $sanitized['remember_token']);
        $this->assertEquals('[REDACTED]', $sanitized['code']);
        $this->assertEquals('[REDACTED]', $sanitized['authorization']);
        $this->assertEquals('[REDACTED]', $sanitized['api_key']);
        $this->assertEquals('admin', $sanitized['role']);
    }

    /**
     * Deeply-nested arrays recursively redact both Master-mandated and existing sensitive keys.
     */
    public function test_deeply_nested_sensitive_keys_are_recursively_redacted(): void
    {
        $payload = [
            'transaction' => [
                'id' => 'TXN-001',
                'payment_details' => [
                    'credit_card' => '4111222233334444',
                    'cvv' => '123',
                    'auth' => [
                        'token' => 'auth_token_999',
                        'otp' => '456789',
                        'credentials' => [
                            'password' => 'nested_secret_pw',
                            'api_key' => 'nested_key',
                        ],
                    ],
                ],
                'safe_info' => [
                    'currency' => 'USD',
                    'total' => '250.00',
                ],
            ],
        ];

        $sanitized = $this->logger->sanitize($payload);

        $this->assertEquals('[REDACTED]', $sanitized['transaction']['payment_details']['credit_card']);
        $this->assertEquals('[REDACTED]', $sanitized['transaction']['payment_details']['cvv']);
        $this->assertEquals('[REDACTED]', $sanitized['transaction']['payment_details']['auth']['token']);
        $this->assertEquals('[REDACTED]', $sanitized['transaction']['payment_details']['auth']['otp']);
        $this->assertEquals('[REDACTED]', $sanitized['transaction']['payment_details']['auth']['credentials']['password']);
        $this->assertEquals('[REDACTED]', $sanitized['transaction']['payment_details']['auth']['credentials']['api_key']);

        $this->assertEquals('TXN-001', $sanitized['transaction']['id']);
        $this->assertEquals('USD', $sanitized['transaction']['safe_info']['currency']);
        $this->assertEquals('250.00', $sanitized['transaction']['safe_info']['total']);
    }

    /**
     * Audit log methods sanitize payload and do not expose passwords or secrets in event data.
     */
    public function test_audit_logger_methods_produce_safe_event_payloads(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $logger = new class extends AuditLogger
        {
            public array $capturedLog = [];

            public function log(
                string $event,
                ?User $user = null,
                mixed $auditable = null,
                ?array $oldValues = null,
                ?array $newValues = null,
                ?Request $request = null
            ): AuditLog {
                $this->capturedLog = [
                    'event' => $event,
                    'user_id' => $user?->id,
                    'old_values' => $oldValues ? $this->sanitize($oldValues) : null,
                    'new_values' => $newValues ? $this->sanitize($newValues) : null,
                ];

                return new AuditLog($this->capturedLog);
            }
        };

        // Test logLogin
        $logger->logLogin($user);
        $this->assertEquals('auth.login', $logger->capturedLog['event']);
        $this->assertEquals('admin@example.com', $logger->capturedLog['new_values']['email']);
        $this->assertArrayNotHasKey('password', $logger->capturedLog['new_values']);

        // Test logFailedLogin
        $logger->logFailedLogin('test@example.com', null, 'invalid_credentials');
        $this->assertEquals('auth.failed', $logger->capturedLog['event']);
        $this->assertEquals('test@example.com', $logger->capturedLog['new_values']['email']);
        $this->assertEquals('invalid_credentials', $logger->capturedLog['new_values']['reason']);

        // Test logLockout
        $logger->logLockout('test@example.com', 60);
        $this->assertEquals('auth.lockout', $logger->capturedLog['event']);
        $this->assertEquals(60, $logger->capturedLog['new_values']['retry_after_seconds']);

        // Test custom sensitive log call
        $logger->log(
            event: 'custom.event',
            user: $user,
            newValues: [
                'password' => 'secret_val',
                'token' => 'tok_val',
                'safe' => 'safe_val',
            ]
        );
        $this->assertEquals('[REDACTED]', $logger->capturedLog['new_values']['password']);
        $this->assertEquals('[REDACTED]', $logger->capturedLog['new_values']['token']);
        $this->assertEquals('safe_val', $logger->capturedLog['new_values']['safe']);
    }
}
