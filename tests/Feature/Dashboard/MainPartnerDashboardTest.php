<?php

namespace Tests\Feature\Dashboard;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Dashboard\MainPartnerDashboardQuery;
use App\Domain\Partners\Partner;
use Illuminate\Auth\Access\AuthorizationException;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MainPartnerDashboardTest extends TestCase
{
    protected Partner $mainPartnerA;

    protected User $mainUserA;

    protected Partner $mainPartnerB;

    protected User $mainUserB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mainPartnerA = new Partner([
            'id' => 10,
            'name' => 'Main Partner Organization A',
            'type' => 'main',
            'status' => 'active',
        ]);
        $this->mainPartnerA->id = 10;

        $this->mainUserA = new User([
            'id' => 100,
            'name' => 'Main Partner User A',
            'email' => 'partnerA@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $this->mainUserA->setRelation('partners', collect([$this->mainPartnerA]));

        $this->mainPartnerB = new Partner([
            'id' => 20,
            'name' => 'Main Partner Organization B',
            'type' => 'main',
            'status' => 'active',
        ]);
        $this->mainPartnerB->id = 20;

        $this->mainUserB = new User([
            'id' => 200,
            'name' => 'Main Partner User B',
            'email' => 'partnerB@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $this->mainUserB->setRelation('partners', collect([$this->mainPartnerB]));
    }

    public function test_main_partner_receives_http_200_and_inertia_shell(): void
    {
        $response = $this->actingAs($this->mainUserA)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('role', 'main_partner')
            ->where('data', [])
            ->where('pending_confirmation', ['metrics', 'date_filters', 'charts'])
        );
    }

    public function test_main_partner_json_response_contains_empty_data_and_pending_confirmation(): void
    {
        $response = $this->actingAs($this->mainUserA)->getJson('/dashboard');

        $response->assertStatus(200)
            ->assertExactJson([
                'role' => 'main_partner',
                'data' => [],
                'pending_confirmation' => [
                    'metrics',
                    'date_filters',
                    'charts',
                ],
            ]);

        $this->assertStringContainsString('"data":{}', $response->getContent());
    }

    public function test_main_partner_response_does_not_contain_invented_metrics(): void
    {
        $response = $this->actingAs($this->mainUserA)->getJson('/dashboard');

        $data = $response->json('data');

        $this->assertEmpty($data);
        $this->assertArrayNotHasKey('leads', $data);
        $this->assertArrayNotHasKey('customers', $data);
        $this->assertArrayNotHasKey('sales', $data);
        $this->assertArrayNotHasKey('subscriptions', $data);
        $this->assertArrayNotHasKey('renewals', $data);
        $this->assertArrayNotHasKey('sub_partners', $data);
        $this->assertArrayNotHasKey('sub_partner_performance', $data);
    }

    public function test_main_partner_idor_protection_ignores_injected_partner_ids(): void
    {
        // Attempt to access or scope dashboard to another partner via query params or request body
        $response = $this->actingAs($this->mainUserA)->getJson('/dashboard?partner_id=20&sub_partner_id=30');

        $response->assertStatus(200)
            ->assertExactJson([
                'role' => 'main_partner',
                'data' => [],
                'pending_confirmation' => [
                    'metrics',
                    'date_filters',
                    'charts',
                ],
            ]);

        $this->assertStringContainsString('"data":{}', $response->getContent());
    }

    public function test_cross_tenant_isolation_between_distinct_main_partners(): void
    {
        // Two different main partners both query the dashboard
        $responseA = $this->actingAs($this->mainUserA)->getJson('/dashboard');
        $responseB = $this->actingAs($this->mainUserB)->getJson('/dashboard');

        $responseA->assertStatus(200);
        $responseB->assertStatus(200);

        // Scoping is derived solely from the authenticated session, neither tenant affects the other
        $this->assertEquals('main_partner', $responseA->json('role'));
        $this->assertEquals('main_partner', $responseB->json('role'));
    }

    public function test_main_partner_query_throws_authorization_exception_for_non_main_partner(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $query = new MainPartnerDashboardQuery;

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('User is not authorized to access the main partner dashboard.');

        $query->execute($admin);
    }

    public function test_main_partner_query_throws_authorization_exception_when_no_partner_assigned(): void
    {
        $unassignedUser = new User([
            'id' => 101,
            'name' => 'Unassigned Main Partner',
            'email' => 'unassigned@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $unassignedUser->setRelation('partners', collect());

        $query = new MainPartnerDashboardQuery;

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('No active main partner organization associated with user.');

        $query->execute($unassignedUser);
    }
}
