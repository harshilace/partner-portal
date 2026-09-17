<?php

namespace Tests\Feature\Notifications;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use Tests\TestCase;

class NotificationAuthorizationTest extends TestCase
{
    public function test_unauthenticated_request_to_notifications_redirects_to_login(): void
    {
        $response = $this->get('/notifications');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_json_request_to_notifications_returns_401(): void
    {
        $response = $this->getJson('/notifications');

        $response->assertStatus(401);
    }

    public function test_inactive_user_is_blocked_from_notifications(): void
    {
        $inactiveUser = new User([
            'id' => 99,
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($inactiveUser)->getJson('/notifications');

        $response->assertStatus(403);
    }

    public function test_active_admin_can_access_notifications(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Active Admin',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);
        $admin->setRelation('notifications', collect([]));
        $admin->setRelation('unreadNotifications', collect([]));

        $response = $this->actingAs($admin)->getJson('/notifications');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'unread_count',
        ]);
    }

    public function test_active_main_partner_can_access_notifications(): void
    {
        $partner = new Partner([
            'id' => 10,
            'partner_code' => 'MAIN-001',
            'name' => 'Main Partner Inc',
            'type' => 'main',
            'status' => 'active',
        ]);

        $user = new User([
            'id' => 2,
            'name' => 'Partner User',
            'email' => 'partner@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $user->setRelation('partners', collect([$partner]));
        $user->setRelation('notifications', collect([]));
        $user->setRelation('unreadNotifications', collect([]));

        $response = $this->actingAs($user)->getJson('/notifications');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'unread_count',
        ]);
    }

    public function test_active_sub_partner_can_access_notifications(): void
    {
        $subPartner = new Partner([
            'id' => 20,
            'partner_code' => 'SUB-001',
            'name' => 'Sub Partner 1',
            'type' => 'sub',
            'parent_partner_id' => 10,
            'status' => 'active',
        ]);

        $user = new User([
            'id' => 3,
            'name' => 'Sub Partner User',
            'email' => 'subpartner@example.com',
            'role' => Role::SUB_PARTNER->value,
            'status' => 'active',
        ]);
        $user->setRelation('partners', collect([$subPartner]));
        $user->setRelation('notifications', collect([]));
        $user->setRelation('unreadNotifications', collect([]));

        $response = $this->actingAs($user)->getJson('/notifications');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'unread_count',
        ]);
    }
}
