<?php

namespace Tests\Feature\Notifications;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Notifications\Notification;
use App\Policies\NotificationPolicy;
use Tests\TestCase;

class NotificationIdorTest extends TestCase
{
    public function test_user_cannot_mark_another_users_notification_as_read_via_http(): void
    {
        $userA = new User([
            'name' => 'User A',
            'email' => 'user_a@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $userA->id = 1;

        $userB = new User([
            'name' => 'User B',
            'email' => 'user_b@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $userB->id = 2;

        $notificationB = new Notification([
            'type' => 'App\\Notifications\\RenewalDueNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $userB->id,
            'data' => ['message' => 'For user B'],
            'read_at' => null,
        ]);
        $notificationB->id = '00000000-0000-0000-0000-000000000002';

        // User A attempts to read User B's notification
        $userA->setRelation('accessibleNotifications', collect([$notificationB]));

        $response = $this->actingAs($userA)
            ->postJson("/notifications/{$notificationB->id}/read");

        $response->assertStatus(403);
    }

    public function test_notification_policy_strictly_enforces_ownership_for_view_and_update(): void
    {
        $policy = new NotificationPolicy;

        $owner = new User([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $owner->id = 10;

        $attacker = new User([
            'name' => 'Attacker',
            'email' => 'attacker@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);
        $attacker->id = 20;

        $notification = new Notification([
            'notifiable_type' => User::class,
            'notifiable_id' => $owner->id,
        ]);
        $notification->id = '00000000-0000-0000-0000-000000000010';

        $this->assertTrue($policy->view($owner, $notification));
        $this->assertTrue($policy->update($owner, $notification));

        $this->assertFalse($policy->view($attacker, $notification));
        $this->assertFalse($policy->update($attacker, $notification));
    }

    public function test_marking_non_existent_notification_returns_404(): void
    {
        $user = new User([
            'name' => 'User',
            'email' => 'user@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);
        $user->id = 1;
        $user->setRelation('notifications', collect([]));

        $response = $this->actingAs($user)
            ->postJson('/notifications/non-existent-uuid/read');

        $response->assertStatus(404);
    }
}
