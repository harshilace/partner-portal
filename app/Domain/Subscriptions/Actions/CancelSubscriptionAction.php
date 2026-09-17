<?php

namespace App\Domain\Subscriptions\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionStatusHistory;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CancelSubscriptionAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Cancel an active subscription (Admin only).
     */
    public function execute(Subscription $subscription, ?string $reason = null, ?User $actor = null): Subscription
    {
        if ($subscription->status === 'cancelled') {
            throw new DomainException('Subscription is already cancelled.');
        }

        return $this->runInTransaction(function () use ($subscription, $reason, $actor) {
            $fromStatus = $subscription->status;
            $toStatus = 'cancelled';
            $now = Carbon::now();

            $this->updateSubscription($subscription, [
                'status' => $toStatus,
                'cancelled_at' => $now,
            ]);

            $this->createSubscriptionStatusHistory([
                'subscription_id' => $subscription->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'changed_by_user_id' => $actor?->id,
                'reason' => $reason,
            ]);

            $this->auditLogger->log(
                'subscription.cancelled',
                $actor,
                $subscription,
                ['status' => $fromStatus],
                ['status' => $toStatus, 'reason' => $reason]
            );

            return $subscription;
        });
    }

    protected function updateSubscription(Subscription $subscription, array $attributes): void
    {
        $subscription->update($attributes);
    }

    protected function runInTransaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    protected function createSubscriptionStatusHistory(array $attributes): SubscriptionStatusHistory
    {
        return SubscriptionStatusHistory::create($attributes);
    }
}
