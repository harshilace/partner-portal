<?php

namespace Tests\Feature\Dashboard;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Dashboard\SubPartnerDashboardQuery;
use App\Domain\Partners\Partner;
use Illuminate\Auth\Access\AuthorizationException;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SubPartnerDashboardTest extends TestCase
{
    protected Partner $subPartnerA;

    protected User $subUserA;

    protected Partner $subPartnerB;

    protected User $subUserB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subPartnerA = new Partner([
            'id' => 101,
            'name' => 'Sub Partner Organization A',
            'type' => 'sub',
            'parent_partner_id' => 10,
            'status' => 'active',
        ]);
        $this->subPartnerA->id = 101;

        $this->subUserA = new User([
            'id' => 1001,
            'name' => 'Sub Partner User A',
            'email' => 'subA@example.com',
            'role' => Role::SUB_PARTNER->value,
            'status' => 'active',
        ]);
        $this->subUserA->setRelation('partners', collect([$this->subPartnerA]));

        $this->subPartnerB = new Partner([
            'id' => 102,
            'name' => 'Sub Partner Organization B',
            'type' => 'sub',
            'parent_partner_id' => 10,
            'status' => 'active',
        ]);
        $this->subPartnerB->id = 102;

        $this->subUserB = new User([
            'id' => 1002,
            'name' => 'Sub Partner User B',
            'email' => 'subB@example.com',
            'role' => Role::SUB_PARTNER->value,
            'status' => 'active',
        ]);
        $this->subUserB->setRelation('partners', collect([$this->subPartnerB]));
    }

    public function test_sub_partner_receives_http_200_and_inertia_shell(): void
    {
        $response = $this->actingAs($this->subUserA)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('role', 'sub_partner')
            ->where('data', [])
            ->where('pending_confirmation', ['metrics', 'date_filters', 'charts'])
        );
    }

    public function test_sub_partner_json_response_contains_empty_data_and_pending_confirmation(): void
    {
        $response = $this->actingAs($this->subUserA)->getJson('/dashboard');

        $response->assertStatus(200)
            ->assertExactJson([
                'role' => 'sub_partner',
                'data' => [],
                'pending_confirmation' => [
                    'metrics',
                    'date_filters',
                    'charts',
                ],
            ]);

        $this->assertStringContainsString('"data":{}', $response->getContent());
    }

    public function test_sub_partner_response_does_not_contain_invented_metrics(): void
    {
        $response = $this->actingAs($this->subUserA)->getJson('/dashboard');

        $data = $response->json('data');

        $this->assertEmpty($data);
        $this->assertArrayNotHasKey('leads', $data);
        $this->assertArrayNotHasKey('customers', $data);
        $this->assertArrayNotHasKey('sales', $data);
        $this->assertArrayNotHasKey('subscriptions', $data);
        $this->assertArrayNotHasKey('renewals', $data);
        $this->assertArrayNotHasKey('other_sub_partners', $data);
    }

    public function test_sub_partner_idor_protection_ignores_injected_tenant_ids(): void
    {
        // Attempt to access or scope dashboard to another sub-partner via query params
        $response = $this->actingAs($this->subUserA)->getJson('/dashboard?partner_id=10&sub_partner_id=102');

        $response->assertStatus(200)
            ->assertExactJson([
                'role' => 'sub_partner',
                'data' => [],
                'pending_confirmation' => [
                    'metrics',
                    'date_filters',
                    'charts',
                ],
            ]);

        $this->assertStringContainsString('"data":{}', $response->getContent());
    }

    public function test_cross_tenant_isolation_between_distinct_sub_partners(): void
    {
        // Two distinct sub-partners query the dashboard
        $responseA = $this->actingAs($this->subUserA)->getJson('/dashboard');
        $responseB = $this->actingAs($this->subUserB)->getJson('/dashboard');

        $responseA->assertStatus(200);
        $responseB->assertStatus(200);

        $this->assertEquals('sub_partner', $responseA->json('role'));
        $this->assertEquals('sub_partner', $responseB->json('role'));
    }

    public function test_sub_partner_query_throws_authorization_exception_for_non_sub_partner(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $query = new SubPartnerDashboardQuery;

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('User is not authorized to access the sub-partner dashboard.');

        $query->execute($admin);
    }

    public function test_sub_partner_query_throws_authorization_exception_when_no_partner_assigned(): void
    {
        $unassignedUser = new User([
            'id' => 1003,
            'name' => 'Unassigned Sub Partner',
            'email' => 'unassigned_sub@example.com',
            'role' => Role::SUB_PARTNER->value,
            'status' => 'active',
        ]);
        $unassignedUser->setRelation('partners', collect());

        $query = new SubPartnerDashboardQuery;

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('No active sub-partner organization associated with user.');

        $query->execute($unassignedUser);
    }
}
