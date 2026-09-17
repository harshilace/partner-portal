<?php

namespace Tests\Feature\Subscriptions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Subscriptions\Actions\CancelSubscriptionAction;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionStatusHistory;
use DomainException;
use Tests\TestCase;

class SubscriptionManagementTest extends TestCase
{
    public function test_admin_can_cancel_subscription_with_reason(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())->method('log');

        $admin = new User(['role' => Role::ADMIN->value, 'status' => 'active']);
        $admin->id = 1;

        $subscription = new Subscription([
            'subscription_number' => 'SUB-12345',
            'status' => 'active',
        ]);
        $subscription->id = 10;

        $action = new class($logger) extends CancelSubscriptionAction
        {
            public ?SubscriptionStatusHistory $history = null;

            protected function runInTransaction(callable $callback): mixed
            {
                return $callback();
            }

            protected function updateSubscription(Subscription $subscription, array $attributes): void
            {
                $subscription->forceFill($attributes);
            }

            protected function createSubscriptionStatusHistory(array $attributes): SubscriptionStatusHistory
            {
                $this->history = new SubscriptionStatusHistory($attributes);

                return $this->history;
            }
        };

        $result = $action->execute($subscription, 'Customer requested cancellation', $admin);

        $this->assertEquals('cancelled', $result->status);
        $this->assertNotNull($result->cancelled_at);

        $this->assertNotNull($action->history);
        $this->assertEquals(10, $action->history->subscription_id);
        $this->assertEquals('active', $action->history->from_status);
        $this->assertEquals('cancelled', $action->history->to_status);
        $this->assertEquals(1, $action->history->changed_by_user_id);
        $this->assertEquals('Customer requested cancellation', $action->history->reason);
    }

    public function test_cancelling_already_cancelled_subscription_throws_domain_exception(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $subscription = new Subscription([
            'id' => 10,
            'status' => 'cancelled',
        ]);

        $action = new CancelSubscriptionAction($logger);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Subscription is already cancelled.');

        $action->execute($subscription, 'Duplicate cancel', $admin);
    }
}
