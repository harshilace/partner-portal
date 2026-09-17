<?php

namespace App\Domain\Audit\Services;

use App\Domain\Audit\AuditLog;
use App\Domain\Authentication\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    /**
     * Sensitive parameter names that must be redacted from audit logs.
     */
    protected const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'token',
        'remember_token',
        'secret',
        'otp',
        'code',
        'authorization',
        'api_key',
        'credit_card',
        'cvv',
    ];

    /**
     * Log an audit event with sanitized values.
     */
    public function log(
        string $event,
        ?User $user = null,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null
    ): AuditLog {
        $ip = $request?->ip() ?? request()?->ip();
        $userAgent = $request?->userAgent() ?? request()?->userAgent();

        return AuditLog::create([
            'user_id' => $user?->id,
            'event' => $event,
            'auditable_type' => $auditable ? $auditable->getMorphClass() : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues ? $this->sanitize($oldValues) : null,
            'new_values' => $newValues ? $this->sanitize($newValues) : null,
            'ip_address' => $ip,
            'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
        ]);
    }

    /**
     * Log a successful authentication event.
     */
    public function logLogin(User $user, ?Request $request = null): AuditLog
    {
        return $this->log(
            event: 'auth.login',
            user: $user,
            auditable: $user,
            newValues: [
                'email' => $user->email,
                'role' => $user->role,
            ],
            request: $request
        );
    }

    /**
     * Log a failed authentication attempt.
     */
    public function logFailedLogin(string $email, ?Request $request = null, ?string $reason = null): AuditLog
    {
        return $this->log(
            event: 'auth.failed',
            newValues: array_filter([
                'email' => $email,
                'reason' => $reason,
            ]),
            request: $request
        );
    }

    /**
     * Log an authentication lockout (rate limiting breach).
     */
    public function logLockout(string $email, int $seconds, ?Request $request = null): AuditLog
    {
        return $this->log(
            event: 'auth.lockout',
            newValues: [
                'email' => $email,
                'retry_after_seconds' => $seconds,
            ],
            request: $request
        );
    }

    /**
     * Log a user logout event.
     */
    public function logLogout(User $user, ?Request $request = null): AuditLog
    {
        return $this->log(
            event: 'auth.logout',
            user: $user,
            auditable: $user,
            request: $request
        );
    }

    /**
     * Recursively sanitize payload arrays by redacting sensitive keys.
     */
    public function sanitize(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $isSensitive = in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true);

            if ($isSensitive) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
