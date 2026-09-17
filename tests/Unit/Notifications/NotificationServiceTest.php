<?php

namespace Tests\Unit\Notifications;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Notifications\Services\NotificationService;
use App\Notifications\NewLeadNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    public function test_dispatch_notifies_each_recipient_in_collection(): void
    {
        Notification::fake();

        $user1 = new User([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);
        $user1->id = 1;

        $user2 = new User([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $user2->id = 2;

        $service = new NotificationService;
        $notification = new NewLeadNotification(null, ['title' => 'New Lead Test']);

        $service->dispatch($notification, [$user1, $user2]);

        Notification::assertSentTo([$user1, $user2], NewLeadNotification::class);
    }

    public function test_service_does_not_accept_external_tenant_scoping(): void
    {
        $reflection = new \ReflectionClass(NotificationService::class);
        $method = $reflection->getMethod('dispatch');
        $params = $method->getParameters();

        $paramNames = array_map(fn ($p) => $p->getName(), $params);

        $this->assertNotContains('partner_id', $paramNames);
        $this->assertNotContains('sub_partner_id', $paramNames);
        $this->assertNotContains('tenant_id', $paramNames);
    }
}
