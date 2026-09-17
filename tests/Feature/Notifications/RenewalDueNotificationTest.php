<?php

namespace Tests\Feature\Notifications;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Notifications\Services\NotificationService;
use App\Domain\Partners\Partner;
use App\Domain\Renewals\Actions\RecordRenewalReminderAction;
use App\Domain\Renewals\Renewal;
use App\Events\RenewalDue;
use App\Notifications\RenewalDueNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RenewalDueNotificationTest extends TestCase
{
    public function test_renewal_due_event_is_fired_after_action_transaction(): void
    {
        Event::fake([RenewalDue::class]);

        $logger = $this->createMock(AuditLogger::class);

        $renewal = new Renewal([
            'status' => 'pending',
            'due_date' => '2026-10-15',
        ]);
        $renewal->id = 10;

        $admin = new User([
            'name' => 'Admin',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);
        $admin->id = 1;

        $action = new class($logger) extends RecordRenewalReminderAction
        {
            protected function runInTransaction(callable $callback): mixed
            {
                return $callback();
            }

            protected function updateRenewal(Renewal $renewal, array $attributes): void
            {
                $renewal->forceFill($attributes);
            }
        };

        $action->execute($renewal, '15d', $admin);

        Event::assertDispatched(RenewalDue::class, function (RenewalDue $event) use ($renewal, $admin) {
            return $event->renewal->id === $renewal->id
                && $event->milestone === '15d'
                && $event->actor?->id === $admin->id;
        });
    }

    public function test_15d_and_7d_milestones_dispatch_to_partner_users_and_stub_customer_delivery(): void
    {
        Notification::fake();

        $partnerUser = new User([
            'name' => 'Partner User',
            'email' => 'partner@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $partnerUser->id = 2;

        $partner = new Partner([
            'name' => 'Partner Org',
            'status' => 'active',
        ]);
        $partner->id = 5;
        $partner->setRelation('users', collect([$partnerUser]));

        $renewal = new Renewal([
            'status' => 'pending',
            'due_date' => '2026-10-15',
        ]);
        $renewal->id = 10;
        $renewal->setRelation('partner', $partner);

        $service = new NotificationService;

        // Test 15d milestone
        $event15d = new RenewalDue($renewal, '15d');
        $service->handleEvent($event15d);

        Notification::assertSentTo(
            $partnerUser,
            RenewalDueNotification::class,
            function (RenewalDueNotification $notification) use ($renewal) {
                return $notification->milestone === '15d'
                    && $notification->renewal->id === $renewal->id;
            }
        );

        // Test 7d milestone
        $event7d = new RenewalDue($renewal, '7d');
        $service->handleEvent($event7d);

        Notification::assertSentTo(
            $partnerUser,
            RenewalDueNotification::class,
            function (RenewalDueNotification $notification) use ($renewal) {
                return $notification->milestone === '7d'
                    && $notification->renewal->id === $renewal->id;
            }
        );
    }

    public function test_30d_and_1d_milestones_remain_unresolved_under_bc_9_01(): void
    {
        Notification::fake();

        $partnerUser = new User([
            'name' => 'Partner User',
            'email' => 'partner@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $partnerUser->id = 2;

        $partner = new Partner([
            'name' => 'Partner Org',
            'status' => 'active',
        ]);
        $partner->id = 5;
        $partner->setRelation('users', collect([$partnerUser]));

        $renewal = new Renewal([
            'status' => 'pending',
            'due_date' => '2026-10-15',
        ]);
        $renewal->id = 10;
        $renewal->setRelation('partner', $partner);

        $service = new NotificationService;

        // 30d milestone recipients are not specified by Master
        $service->handleEvent(new RenewalDue($renewal, '30d'));

        // 1d milestone recipients are not specified by Master
        $service->handleEvent(new RenewalDue($renewal, '1d'));

        Notification::assertNothingSent();
    }
}
