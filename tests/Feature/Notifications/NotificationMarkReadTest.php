<?php

namespace Tests\Feature\Notifications;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Notifications\Notification;
use Tests\TestCase;

class NotificationMarkReadTest extends TestCase
{
    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = new User([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);
        $user->id = 10;

        $notification = new Notification([
            'type' => 'App\\Notifications\\RenewalDueNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['title' => 'Renewal Due'],
            'read_at' => null,
        ]);
        $notification->id = '00000000-0000-0000-0000-000000000010';

        $user->setRelation('notifications', collect([$notification]));

        $response = $this->actingAs($user)
            ->postJson("/notifications/{$notification->id}/read");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Notification marked as read.',
        ]);

        $this->assertTrue($notification->isRead());
        $this->assertNotNull($notification->read_at);
    }

    public function test_user_can_mark_all_unread_notifications_as_read(): void
    {
        $user = new User([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);
        $user->id = 10;

        $notification1 = new Notification([
            'type' => 'App\\Notifications\\SaleCompletedNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['title' => 'Sale Completed'],
            'read_at' => null,
        ]);
        $notification1->id = '00000000-0000-0000-0000-000000000011';

        $notification2 = new Notification([
            'type' => 'App\\Notifications\\PaymentReceivedNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['title' => 'Payment Received'],
            'read_at' => null,
        ]);
        $notification2->id = '00000000-0000-0000-0000-000000000012';

        $user->setRelation('unreadNotifications', collect([$notification1, $notification2]));

        $response = $this->actingAs($user)
            ->postJson('/notifications/read-all');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'All notifications marked as read.',
        ]);

        $this->assertTrue($notification1->isRead());
        $this->assertTrue($notification2->isRead());
    }
}
