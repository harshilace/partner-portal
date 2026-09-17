<?php

namespace Tests\Feature\Subscriptions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Subscriptions\Actions\StopAutoDebitAction;
use App\Domain\Subscriptions\AutoDebitEvent;
use App\Domain\Subscriptions\AutoDebitMandate;
use App\Domain\Subscriptions\Subscription;
use DomainException;
use Tests\TestCase;

class AutoDebitManagementTest extends TestCase
{
    public function test_admin_can_stop_active_auto_debit_mandate(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                'auto_debit.stopped',
                $this->anything(),
                $this->anything(),
                ['status' => 'active'],
                $this->callback(fn ($values) => $values['status'] === 'stopped' && $values['reason'] === 'Customer request via Admin')
            );

        $admin = new User(['role' => Role::ADMIN->value, 'status' => 'active']);
        $admin->id = 1;

        $subscription = new Subscription([
            'status' => 'active',
            'auto_debit_enabled' => true,
        ]);
        $subscription->id = 10;

        $mandate = new AutoDebitMandate([
            'subscription_id' => 10,
            'customer_id' => 5,
            'mandate_reference' => 'MANDATE-REF-001',
            'status' => 'active',
        ]);
        $mandate->id = 100;
        $mandate->setRelation('subscription', $subscription);

        $action = new class($logger) extends StopAutoDebitAction
        {
            public ?AutoDebitEvent $recordedEvent = null;

            public array $mandateUpdates = [];

            public array $subscriptionUpdates = [];

            protected function runInTransaction(callable $callback): mixed
            {
                return $callback();
            }

            protected function updateMandate(AutoDebitMandate $mandate, array $attributes): void
            {
                $this->mandateUpdates = $attributes;
                $mandate->forceFill($attributes);
            }

            protected function updateSubscription(Subscription $subscription, array $attributes): void
            {
                $this->subscriptionUpdates = $attributes;
                $subscription->forceFill($attributes);
            }

            protected function createAutoDebitEvent(array $attributes): AutoDebitEvent
            {
                $this->recordedEvent = new AutoDebitEvent($attributes);

                return $this->recordedEvent;
            }
        };

        $result = $action->execute($mandate, 'Customer request via Admin', $admin);

        $this->assertEquals('stopped', $result->status);
        $this->assertEquals(1, $result->stopped_by_user_id);
        $this->assertNotNull($result->stopped_at);
        $this->assertEquals('Customer request via Admin', $result->stop_reason);

        // Verify parent subscription auto_debit_enabled is atomically disabled
        $this->assertFalse($subscription->auto_debit_enabled);
        $this->assertFalse($action->subscriptionUpdates['auto_debit_enabled']);

        // Verify auto_debit_events record
        $this->assertNotNull($action->recordedEvent);
        $this->assertEquals(100, $action->recordedEvent->auto_debit_mandate_id);
        $this->assertEquals(10, $action->recordedEvent->subscription_id);
        $this->assertEquals('stopped', $action->recordedEvent->event_type);
        $this->assertEquals(1, $action->recordedEvent->performed_by_user_id);
        $this->assertEquals('Customer request via Admin', $action->recordedEvent->details['reason']);
    }

    public function test_stopping_already_stopped_mandate_throws_domain_exception(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $mandate = new AutoDebitMandate([
            'status' => 'stopped',
        ]);
        $mandate->id = 100;

        $action = new StopAutoDebitAction($logger);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Auto-debit mandate is already stopped.');

        $action->execute($mandate, 'Duplicate stop', $admin);
    }
}
