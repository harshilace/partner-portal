<?php

namespace Tests\Feature\Auth;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Actions\AuthenticateUserAction;
use App\Domain\Authentication\Actions\LogoutUserAction;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Http\Middleware\EnsureActiveUser;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    protected AuditLogger $auditLogger;

    protected AuthenticateUserAction $authenticateAction;

    protected LogoutUserAction $logoutAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auditLogger = $this->createMock(AuditLogger::class);
        $this->authenticateAction = new AuthenticateUserAction($this->auditLogger);
        $this->logoutAction = new LogoutUserAction($this->auditLogger);

        RateLimiter::clear('admin@example.com|127.0.0.1');
        RateLimiter::clear('main@example.com|127.0.0.1');
        RateLimiter::clear('sub@example.com|127.0.0.1');
    }

    public function test_admin_can_login_successfully(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'admin@example.com', 'password' => 'password123', 'status' => 'active'], false)
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->andReturn($user);

        $this->auditLogger->expects($this->once())
            ->method('logLogin')
            ->with($user);

        $result = $this->authenticateAction->execute('admin@example.com', 'password123');

        $this->assertSame($user, $result);
        $this->assertTrue($result->isAdmin());
    }

    public function test_main_partner_can_login_successfully(): void
    {
        $user = new User([
            'id' => 2,
            'name' => 'Main Partner User',
            'email' => 'main@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'main@example.com', 'password' => 'secretMain', 'status' => 'active'], false)
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->andReturn($user);

        $this->auditLogger->expects($this->once())
            ->method('logLogin')
            ->with($user);

        $result = $this->authenticateAction->execute('main@example.com', 'secretMain');

        $this->assertSame($user, $result);
        $this->assertTrue($result->isMainPartner());
    }

    public function test_sub_partner_can_login_successfully(): void
    {
        $user = new User([
            'id' => 3,
            'name' => 'Sub Partner User',
            'email' => 'sub@example.com',
            'role' => Role::SUB_PARTNER->value,
            'status' => 'active',
        ]);

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'sub@example.com', 'password' => 'secretSub', 'status' => 'active'], false)
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->andReturn($user);

        $this->auditLogger->expects($this->once())
            ->method('logLogin')
            ->with($user);

        $result = $this->authenticateAction->execute('sub@example.com', 'secretSub');

        $this->assertSame($user, $result);
        $this->assertTrue($result->isSubPartner());
    }

    public function test_login_rejected_for_invalid_password(): void
    {
        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'user@example.com', 'password' => 'wrongpass', 'status' => 'active'], false)
            ->andReturn(false);

        $this->auditLogger->expects($this->once())
            ->method('logFailedLogin')
            ->with('user@example.com');

        $this->expectException(ValidationException::class);

        $this->authenticateAction->execute('user@example.com', 'wrongpass');
    }

    public function test_login_rejected_for_inactive_account(): void
    {
        // Auth::attempt checks ['status' => 'active'], which returns false for inactive users
        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'inactive@example.com', 'password' => 'correctpass', 'status' => 'active'], false)
            ->andReturn(false);

        $this->auditLogger->expects($this->once())
            ->method('logFailedLogin')
            ->with('inactive@example.com');

        $this->expectException(ValidationException::class);

        $this->authenticateAction->execute('inactive@example.com', 'correctpass');
    }

    public function test_logout_terminates_session_and_logs_audit(): void
    {
        $user = new User(['id' => 1, 'email' => 'admin@example.com', 'status' => 'active', 'role' => 'admin']);

        $guard = $this->createMock(StatefulGuard::class);
        $guard->expects($this->once())->method('logout');

        Auth::shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('guard')->with('web')->andReturn($guard);

        $this->auditLogger->expects($this->once())
            ->method('logLogout')
            ->with($user);

        $this->logoutAction->execute();
    }

    public function test_http_login_endpoint_returns_json_on_successful_authentication(): void
    {
        $user = new User([
            'name' => 'API Admin',
            'email' => 'api@example.com',
            'role' => 'admin',
            'status' => 'active',
        ]);
        $user->id = 10;

        $mockAction = $this->createMock(AuthenticateUserAction::class);
        $mockAction->expects($this->once())
            ->method('execute')
            ->willReturn($user);

        $this->app->instance(AuthenticateUserAction::class, $mockAction);

        $response = $this->postJson('/login', [
            'email' => 'api@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Authenticated successfully.',
                'user' => [
                    'id' => 10,
                    'name' => 'API Admin',
                    'email' => 'api@example.com',
                    'role' => 'admin',
                    'status' => 'active',
                ],
            ]);
    }

    public function test_http_logout_endpoint_returns_json(): void
    {
        $mockLogoutAction = $this->createMock(LogoutUserAction::class);
        $mockLogoutAction->expects($this->once())
            ->method('execute');

        $this->app->instance(LogoutUserAction::class, $mockLogoutAction);

        $response = $this->postJson('/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully.',
            ]);
    }

    public function test_ensure_active_user_middleware_blocks_inactive_user(): void
    {
        $inactiveUser = new User([
            'id' => 99,
            'name' => 'Inactive User',
            'email' => 'suspended@example.com',
            'status' => 'inactive',
            'role' => 'partner_user',
        ]);

        $guard = $this->createMock(StatefulGuard::class);
        $guard->expects($this->once())->method('logout');
        Auth::shouldReceive('guard')->with('web')->andReturn($guard);

        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $inactiveUser);
        $request->headers->set('Accept', 'application/json');

        $middleware = new EnsureActiveUser;
        $response = $middleware->handle($request, function () {
            $this->fail('Request should have been blocked by middleware');
        });

        $this->assertEquals(403, $response->getStatusCode());
    }
}
