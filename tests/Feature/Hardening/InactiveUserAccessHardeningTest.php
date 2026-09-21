<?php

namespace Tests\Feature\Hardening;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Actions\AuthenticateUserAction;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InactiveUserAccessHardeningTest extends TestCase
{
    /**
     * The 15 protected route groups under auth + active middleware.
     */
    protected const PROTECTED_ROUTES = [
        '/partners',
        '/partners/1/sub-partners',
        '/referral-codes',
        '/leads',
        '/customers',
        '/products',
        '/orders',
        '/subscriptions',
        '/auto-debit-mandates',
        '/renewals',
        '/notifications',
        '/reports/sales',
        '/reports/customers',
        '/reports/renewals',
        '/audit',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Route::bind('partner', function ($id) {
            $partner = new Partner(['type' => 'main']);
            $partner->id = (int) $id;

            return $partner;
        });
    }

    /**
     * Inactive user is rejected with HTTP 403 on all 15 protected route groups via JSON.
     */
    public function test_inactive_user_is_denied_json_access_across_all_15_protected_route_groups(): void
    {
        $inactiveUser = new User([
            'id' => 999,
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'inactive',
        ]);

        foreach (self::PROTECTED_ROUTES as $route) {
            $response = $this->actingAs($inactiveUser)->getJson($route);

            $this->assertEquals(
                403,
                $response->getStatusCode(),
                "Route [{$route}] did not return 403 for inactive user."
            );
            $response->assertJson(['message' => 'Account is inactive.']);
        }
    }

    /**
     * Inactive user is redirected to login on all 15 protected route groups via web.
     */
    public function test_inactive_user_is_redirected_to_login_across_all_15_protected_route_groups(): void
    {
        $inactiveUser = new User([
            'id' => 999,
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'inactive',
        ]);

        foreach (self::PROTECTED_ROUTES as $route) {
            $response = $this->actingAs($inactiveUser)->get($route);

            $this->assertEquals(
                302,
                $response->getStatusCode(),
                "Route [{$route}] did not redirect 302 for inactive user."
            );
            $response->assertRedirect('/login');
        }
    }

    /**
     * Inactive user cannot authenticate through authentication action.
     */
    public function test_inactive_user_cannot_authenticate_via_credentials(): void
    {
        $auditLogger = $this->createMock(AuditLogger::class);
        $auditLogger->expects($this->once())->method('logFailedLogin');

        $action = new AuthenticateUserAction($auditLogger);

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'inactive@example.com', 'password' => 'secret123', 'status' => 'active'], false)
            ->andReturn(false);

        $this->expectException(ValidationException::class);
        $action->execute('inactive@example.com', 'secret123');
    }
}
