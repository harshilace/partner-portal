<?php

namespace Tests\Feature\Dashboard;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use Tests\TestCase;

class DashboardAuthorizationTest extends TestCase
{
    public function test_unauthenticated_request_to_dashboard_redirects_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_inactive_user_is_blocked_from_dashboard(): void
    {
        $inactiveUser = new User([
            'id' => 99,
            'name' => 'Inactive Admin',
            'email' => 'inactive@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($inactiveUser)->get('/dashboard');

        // Middleware logs out and redirects to /login or returns 403 for json
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_inactive_user_with_json_accept_header_is_rejected_with_403(): void
    {
        $inactiveUser = new User([
            'id' => 99,
            'name' => 'Inactive Admin',
            'email' => 'inactive@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($inactiveUser)->getJson('/dashboard');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Account is inactive.']);
    }

    public function test_active_admin_user_can_access_dashboard(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_active_main_partner_user_with_partner_can_access_dashboard(): void
    {
        $mainPartner = new Partner(['id' => 10, 'type' => 'main', 'status' => 'active']);
        $mainPartner->id = 10;

        $mainUser = new User([
            'id' => 2,
            'name' => 'Main Partner User',
            'email' => 'main@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $mainUser->setRelation('partners', collect([$mainPartner]));

        $response = $this->actingAs($mainUser)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_active_sub_partner_user_with_partner_can_access_dashboard(): void
    {
        $subPartner = new Partner(['id' => 20, 'type' => 'sub', 'parent_partner_id' => 10, 'status' => 'active']);
        $subPartner->id = 20;

        $subUser = new User([
            'id' => 3,
            'name' => 'Sub Partner User',
            'email' => 'sub@example.com',
            'role' => Role::SUB_PARTNER->value,
            'status' => 'active',
        ]);
        $subUser->setRelation('partners', collect([$subPartner]));

        $response = $this->actingAs($subUser)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_user_without_assigned_partner_is_forbidden_from_dashboard(): void
    {
        $unassignedUser = new User([
            'id' => 4,
            'name' => 'Unassigned Main Partner',
            'email' => 'unassigned@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $unassignedUser->setRelation('partners', collect());

        $response = $this->actingAs($unassignedUser)->get('/dashboard');

        $response->assertStatus(403);
    }

    public function test_partner_user_without_type_is_forbidden_from_dashboard(): void
    {
        $plainUser = new User([
            'id' => 5,
            'name' => 'Generic Partner User',
            'email' => 'generic@example.com',
            'role' => Role::PARTNER_USER->value,
            'status' => 'active',
        ]);
        $plainUser->setRelation('partners', collect());

        $response = $this->actingAs($plainUser)->get('/dashboard');

        $response->assertStatus(403);
    }
}
