<?php

namespace Tests\Feature\Reports;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use Tests\TestCase;

class RenewalReportTest extends TestCase
{
    public function test_unauthenticated_request_to_renewal_report_redirects_to_login(): void
    {
        $response = $this->get('/reports/renewals');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_json_request_to_renewal_report_returns_401(): void
    {
        $response = $this->getJson('/reports/renewals');

        $response->assertStatus(401);
    }

    public function test_inactive_user_cannot_access_renewal_report(): void
    {
        $inactiveUser = new User([
            'id' => 99,
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($inactiveUser)->getJson('/reports/renewals');

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Account is inactive.']);
    }

    public function test_active_admin_can_access_renewal_report_endpoint_and_receives_blocked_stub_501(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->getJson('/reports/renewals');

        $response->assertStatus(501);
        $response->assertJson([
            'status' => 501,
            'message' => 'Renewal report data retrieval is blocked pending business confirmation.',
            'blocked' => true,
            'blocked_items' => [
                'BC-10-01',
                'BC-10-04',
                'BC-10-05',
                'BC-10-06',
                'BC-10-09',
            ],
        ]);
    }

    public function test_active_main_partner_can_access_renewal_report_endpoint_and_receives_blocked_stub_501(): void
    {
        $partner = new Partner([
            'id' => 10,
            'partner_code' => 'MAIN-001',
            'name' => 'Main Partner Inc',
            'type' => 'main',
            'status' => 'active',
        ]);

        $mainUser = new User([
            'id' => 2,
            'name' => 'Main Partner User',
            'email' => 'main@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $mainUser->setRelation('partners', collect([$partner]));

        $response = $this->actingAs($mainUser)->getJson('/reports/renewals');

        $response->assertStatus(501);
        $response->assertJson([
            'status' => 501,
            'blocked' => true,
        ]);
    }

    public function test_active_sub_partner_can_access_renewal_report_endpoint_and_receives_blocked_stub_501(): void
    {
        $subPartner = new Partner([
            'id' => 20,
            'partner_code' => 'SUB-001',
            'name' => 'Sub Partner LLC',
            'type' => 'sub',
            'parent_partner_id' => 10,
            'status' => 'active',
        ]);

        $subUser = new User([
            'id' => 3,
            'name' => 'Sub Partner User',
            'email' => 'sub@example.com',
            'role' => Role::SUB_PARTNER->value,
            'status' => 'active',
        ]);
        $subUser->setRelation('partners', collect([$subPartner]));

        $response = $this->actingAs($subUser)->getJson('/reports/renewals');

        $response->assertStatus(501);
        $response->assertJson([
            'status' => 501,
            'blocked' => true,
        ]);
    }

    public function test_unauthorized_role_cannot_access_renewal_report(): void
    {
        $user = new User([
            'id' => 4,
            'name' => 'Generic Partner User',
            'email' => 'generic@example.com',
            'role' => Role::PARTNER_USER->value,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->getJson('/reports/renewals');

        $response->assertStatus(403);
    }
}
