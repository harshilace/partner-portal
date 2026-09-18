<?php

namespace Tests\Feature\Audit;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use Tests\TestCase;

class AuditControllerTest extends TestCase
{
    /**
     * Unauthenticated users cannot access the audit endpoint.
     */
    public function test_unauthenticated_cannot_access_audit_index(): void
    {
        $response = $this->get('/audit');
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $jsonResponse = $this->getJson('/audit');
        $jsonResponse->assertStatus(401);
    }

    /**
     * Inactive users cannot access the audit endpoint.
     */
    public function test_inactive_user_cannot_access_audit_index(): void
    {
        $inactiveUser = new User([
            'id' => 99,
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($inactiveUser)->getJson('/audit');

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Account is inactive.']);
    }

    /**
     * Admin user can access audit endpoint and reaches the 501 blocked stub.
     */
    public function test_admin_can_access_audit_index_and_receives_501_stub(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->getJson('/audit');

        $response->assertStatus(501);
        $response->assertJson([
            'message' => 'Audit log retrieval is not yet implemented.',
            'blocked_by' => [
                'BC-11-01',
                'BC-11-02',
                'BC-11-03',
                'BC-11-04',
            ],
        ]);
    }

    /**
     * Main Partner receives 403 under temporary security default pending BC-11-01.
     * Note: This is NOT a confirmed business requirement.
     */
    public function test_main_partner_cannot_access_audit_index(): void
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

        $response = $this->actingAs($mainUser)->getJson('/audit');

        $response->assertStatus(403);
    }

    /**
     * Sub-Partner receives 403 under temporary security default pending BC-11-01.
     * Note: This is NOT a confirmed business requirement.
     */
    public function test_sub_partner_cannot_access_audit_index(): void
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

        $response = $this->actingAs($subUser)->getJson('/audit');

        $response->assertStatus(403);
    }
}
