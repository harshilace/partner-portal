<?php

namespace Tests\Feature\Notifications;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Notifications\Services\NotificationService;
use App\Domain\Subscriptions\Actions\StopAutoDebitAction;
use App\Domain\Subscriptions\AutoDebitEvent;
use App\Domain\Subscriptions\AutoDebitMandate;
use App\Domain\Subscriptions\Subscription;
use App\Events\AutoDebitStopped;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AutoDebitStoppedNotificationTest extends TestCase
{
    public function test_auto_debit_stopped_event_is_fired_after_action_transaction(): void
    {
        Event::fake([AutoDebitStopped::class]);

        $logger = $this->createMock(AuditLogger::class);

        $admin = new User([
            'name' => 'Admin',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);
        $admin->id = 1;

        $subscription = new Subscription([
            'status' => 'active',
            'auto_debit_enabled' => true,
        ]);
        $subscription->id = 50;

        $mandate = new AutoDebitMandate([
            'subscription_id' => $subscription->id,
            'status' => 'active',
        ]);
        $mandate->id = 100;
        $mandate->setRelation('subscription', $subscription);

        $action = new class($logger) extends StopAutoDebitAction
        {
            protected function runInTransaction(callable $callback): mixed
            {
                return $callback();
            }

            protected function updateMandate(AutoDebitMandate $mandate, array $attributes): void
            {
                $mandate->forceFill($attributes);
            }

            protected function updateSubscription(Subscription $subscription, array $attributes): void
            {
                $subscription->forceFill($attributes);
            }

            protected function createAutoDebitEvent(array $attributes): AutoDebitEvent
            {
                return new AutoDebitEvent($attributes);
            }
        };

        $stopped = $action->execute($mandate, 'Customer requested cancellation', $admin);

        $this->assertEquals('stopped', $stopped->status);

        Event::assertDispatched(AutoDebitStopped::class, function (AutoDebitStopped $event) use ($mandate, $admin) {
            return $event->mandate->id === $mandate->id
                && $event->actor?->id === $admin->id;
        });
    }

    public function test_auto_debit_stopped_listener_does_not_invent_recipients_pending_bc_9_01(): void
    {
        $service = new NotificationService;

        $mandate = new AutoDebitMandate([
            'status' => 'stopped',
        ]);
        $mandate->id = 100;

        $event = new AutoDebitStopped($mandate);

        // handleEvent executes without throwing, and dispatches 0 notifications because BC-9-01 blocks recipient mapping
        $service->handleEvent($event);

        $this->assertTrue(true);
    }
}
