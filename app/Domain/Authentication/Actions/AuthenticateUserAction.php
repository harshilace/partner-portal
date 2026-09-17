<?php

namespace App\Domain\Authentication\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticateUserAction
{
    public const MAX_ATTEMPTS = 5;

    public const DECAY_SECONDS = 60;

    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Execute user authentication with rate limiting, active status check, session fixation protection, and audit logging.
     *
     * @throws ValidationException
     */
    public function execute(string $email, string $password, bool $remember = false, ?Request $request = null): User
    {
        $normalizedEmail = Str::lower(trim($email));
        $throttleKey = $this->throttleKey($normalizedEmail, $request);

        $this->ensureIsNotRateLimited($throttleKey, $normalizedEmail, $request);

        // Verify credentials with active user constraint
        if (! Auth::attempt(['email' => $normalizedEmail, 'password' => $password, 'status' => 'active'], $remember)) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            $this->auditLogger->logFailedLogin($normalizedEmail, $request, 'invalid_credentials_or_inactive');

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        if ($request?->hasSession()) {
            $request->session()->regenerate();
        }

        /** @var User $user */
        $user = Auth::user();

        $this->auditLogger->logLogin($user, $request);

        return $user;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    protected function ensureIsNotRateLimited(string $throttleKey, string $email, ?Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($throttleKey);

        $this->auditLogger->logLockout($email, $seconds, $request);

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]),
        ])->status(429);
    }

    /**
     * Generate throttle key for rate limiting based on email and client IP.
     */
    public function throttleKey(string $email, ?Request $request): string
    {
        $ip = $request?->ip() ?? request()?->ip() ?? '127.0.0.1';

        return Str::transliterate(Str::lower($email).'|'.$ip);
    }
}
