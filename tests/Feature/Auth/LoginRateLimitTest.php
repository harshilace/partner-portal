<?php

namespace Tests\Feature\Auth;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Actions\AuthenticateUserAction;
use App\Domain\Authentication\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    protected AuthenticateUserAction $action;

    protected AuditLogger $auditLogger;

    protected string $email = 'ratelimit@example.com';

    protected Request $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auditLogger = $this->createMock(AuditLogger::class);
        $this->action = new AuthenticateUserAction($this->auditLogger);

        $this->request = Request::create('/login', 'POST', [
            'email' => $this->email,
            'password' => 'wrongpassword',
        ], server: ['REMOTE_ADDR' => '10.0.0.1']);

        RateLimiter::clear($this->action->throttleKey($this->email, $this->request));
    }

    protected function tearDown(): void
    {
        RateLimiter::clear($this->action->throttleKey($this->email, $this->request));
        parent::tearDown();
    }

    public function test_five_failed_attempts_allowed_before_lockout(): void
    {
        Auth::shouldReceive('attempt')->times(5)->andReturn(false);

        for ($i = 1; $i <= 5; $i++) {
            try {
                $this->action->execute($this->email, 'wrongpass', false, $this->request);
                $this->fail("Expected ValidationException on attempt {$i}");
            } catch (ValidationException $e) {
                $this->assertEquals(422, $e->status);
                $this->assertArrayHasKey('email', $e->errors());
            }
        }
    }

    public function test_sixth_attempt_triggers_429_lockout(): void
    {
        Auth::shouldReceive('attempt')->times(5)->andReturn(false);

        for ($i = 1; $i <= 5; $i++) {
            try {
                $this->action->execute($this->email, 'wrongpass', false, $this->request);
            } catch (ValidationException) {
                // Expected failed attempt
            }
        }

        // Expect audit logger to log lockout
        $this->auditLogger->expects($this->once())
            ->method('logLockout')
            ->with($this->email, $this->greaterThan(0), $this->request);

        // 6th attempt should trigger 429 lockout without calling Auth::attempt
        try {
            $this->action->execute($this->email, 'wrongpass', false, $this->request);
            $this->fail('Expected 429 ValidationException on 6th attempt');
        } catch (ValidationException $e) {
            $this->assertEquals(429, $e->status);
            $this->assertStringContainsString('Too many login attempts', $e->errors()['email'][0]);
        }
    }

    public function test_successful_login_clears_rate_limiter(): void
    {
        $throttleKey = $this->action->throttleKey($this->email, $this->request);
        RateLimiter::hit($throttleKey);
        RateLimiter::hit($throttleKey);
        $this->assertEquals(2, RateLimiter::attempts($throttleKey));

        $user = new User(['id' => 1, 'email' => $this->email, 'status' => 'active', 'role' => 'admin']);

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => $this->email, 'password' => 'correctpass', 'status' => 'active'], false)
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->andReturn($user);

        $this->auditLogger->expects($this->once())
            ->method('logLogin')
            ->with($user, $this->request);

        $result = $this->action->execute($this->email, 'correctpass', false, $this->request);

        $this->assertSame($user, $result);
        $this->assertEquals(0, RateLimiter::attempts($throttleKey));
    }
}
