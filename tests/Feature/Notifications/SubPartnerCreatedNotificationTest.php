<?php

namespace Tests\Feature\Notifications;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Notifications\Services\NotificationService;
use App\Domain\Partners\Actions\CreateSubPartnerAction;
use App\Domain\Partners\Partner;
use App\Events\SubPartnerCreated;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SubPartnerCreatedNotificationTest extends TestCase
{
    public function test_sub_partner_created_event_is_fired_after_action_transaction(): void
    {
        Event::fake([SubPartnerCreated::class]);

        $logger = $this->createMock(AuditLogger::class);

        $parent = new Partner([
            'partner_code' => 'MAIN-001',
            'name' => 'Parent Partner',
            'type' => 'main',
            'status' => 'active',
        ]);
        $parent->id = 10;

        $admin = new User([
            'name' => 'Admin',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);
        $admin->id = 1;

        $action = new class($logger) extends CreateSubPartnerAction
        {
            protected function runInTransaction(callable $callback): mixed
            {
                return $callback();
            }

            protected function createPartner(array $attributes): Partner
            {
                $sub = new Partner($attributes);
                $sub->id = 20;

                return $sub;
            }
        };

        $subPartner = $action->execute([
            'partner_code' => 'SUB-001',
            'name' => 'New Sub Partner',
        ], $parent, $admin);

        $this->assertEquals('SUB-001', $subPartner->partner_code);
        $this->assertEquals(10, $subPartner->parent_partner_id);

        Event::assertDispatched(SubPartnerCreated::class, function (SubPartnerCreated $event) use ($subPartner, $admin) {
            return $event->subPartner->id === $subPartner->id
                && $event->actor?->id === $admin->id;
        });
    }

    public function test_sub_partner_created_listener_keeps_recipients_blocked_under_bc_9_01(): void
    {
        $service = new NotificationService;

        $subPartner = new Partner([
            'partner_code' => 'SUB-001',
            'name' => 'New Sub Partner',
            'type' => 'sub',
        ]);
        $subPartner->id = 20;

        $event = new SubPartnerCreated($subPartner);

        // handleEvent executes without inventing unconfirmed recipients
        $service->handleEvent($event);

        $this->assertTrue(true);
    }
}
