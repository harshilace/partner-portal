<?php

namespace Tests\Feature\UI;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NavigationViewsTest extends TestCase
{
    protected const NAVIGATION_ROUTES = [
        '/products',
        '/referral-codes',
        '/leads',
        '/customers',
        '/orders',
        '/subscriptions',
        '/auto-debit-mandates',
        '/renewals',
        '/notifications',
        '/reports/sales',
        '/audit',
    ];

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);
    }

    public function test_unauthenticated_user_redirected_to_login_for_all_navigation_routes(): void
    {
        foreach (self::NAVIGATION_ROUTES as $route) {
            $response = $this->get($route);
            $response->assertStatus(302);
            $response->assertRedirect('/login');
        }
    }

    public function test_admin_can_access_notifications_view(): void
    {
        $this->admin->setRelation('notifications', collect());
        $this->admin->setRelation('unreadNotifications', collect());

        $response = $this->actingAs($this->admin)->get('/notifications');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Notifications/Index'));
    }

    public function test_admin_can_access_reports_view(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/sales');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Reports/Index'));
    }

    public function test_admin_can_access_audit_view(): void
    {
        $response = $this->actingAs($this->admin)->get('/audit');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Audit/Index'));
    }
}
