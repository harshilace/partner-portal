<?php

namespace Tests\Feature\Dashboard;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Dashboard\AdminDashboardQuery;
use Illuminate\Auth\Access\AuthorizationException;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
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

    public function test_admin_receives_http_200_and_inertia_shell(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('role', 'admin')
            ->where('data', [])
            ->where('pending_confirmation', ['metrics', 'date_filters', 'charts'])
        );
    }

    public function test_admin_json_response_contains_empty_data_and_pending_confirmation(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/dashboard');

        $response->assertStatus(200)
            ->assertExactJson([
                'role' => 'admin',
                'data' => [],
                'pending_confirmation' => [
                    'metrics',
                    'date_filters',
                    'charts',
                ],
            ]);

        // Raw serialized JSON must be empty object {} rather than empty array []
        $this->assertStringContainsString('"data":{}', $response->getContent());
    }

    public function test_admin_response_does_not_contain_invented_metrics(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/dashboard');

        $data = $response->json('data');

        $this->assertEmpty($data);
        $this->assertArrayNotHasKey('partners', $data);
        $this->assertArrayNotHasKey('sub_partners', $data);
        $this->assertArrayNotHasKey('customers', $data);
        $this->assertArrayNotHasKey('leads', $data);
        $this->assertArrayNotHasKey('subscriptions', $data);
        $this->assertArrayNotHasKey('renewals', $data);
        $this->assertArrayNotHasKey('orders', $data);
        $this->assertArrayNotHasKey('auto_debit_mandates', $data);
        $this->assertArrayNotHasKey('revenue', $data);
        $this->assertArrayNotHasKey('conversion_rate', $data);
    }

    public function test_admin_dashboard_ignores_tenant_id_in_request_parameters(): void
    {
        // IDOR attempt / parameter injection should be completely ignored
        $response = $this->actingAs($this->admin)->getJson('/dashboard?partner_id=999&sub_partner_id=888');

        $response->assertStatus(200)
            ->assertExactJson([
                'role' => 'admin',
                'data' => [],
                'pending_confirmation' => [
                    'metrics',
                    'date_filters',
                    'charts',
                ],
            ]);

        $this->assertStringContainsString('"data":{}', $response->getContent());
    }

    public function test_admin_dashboard_query_throws_authorization_exception_for_non_admin(): void
    {
        $nonAdmin = new User([
            'id' => 2,
            'name' => 'Non Admin',
            'email' => 'partner@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);

        $query = new AdminDashboardQuery;

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('User is not authorized to access the admin dashboard.');

        $query->execute($nonAdmin);
    }
}
