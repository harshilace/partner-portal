<?php

namespace App\Domain\Subscriptions\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Subscriptions\AutoDebitEvent;
use App\Domain\Subscriptions\AutoDebitMandate;
use App\Domain\Subscriptions\Subscription;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StopAutoDebitAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Stop an active auto-debit mandate (Admin only).
     */
    public function execute(AutoDebitMandate $mandate, string $reason, ?User $actor = null): AutoDebitMandate
    {
        if ($mandate->isStopped() || $mandate->status === 'stopped') {
            throw new DomainException('Auto-debit mandate is already stopped.');
        }

        return $this->runInTransaction(function () use ($mandate, $reason, $actor) {
            $fromStatus = $mandate->status;
            $toStatus = 'stopped';
            $now = Carbon::now();

            $this->updateMandate($mandate, [
                'status' => $toStatus,
                'stopped_by_user_id' => $actor?->id,
                'stopped_at' => $now,
                'stop_reason' => $reason,
            ]);

            // Atomically disable auto_debit_enabled on parent subscription
            if ($mandate->subscription) {
                $this->updateSubscription($mandate->subscription, [
                    'auto_debit_enabled' => false,
                ]);
            }

            $this->createAutoDebitEvent([
                'auto_debit_mandate_id' => $mandate->id,
                'subscription_id' => $mandate->subscription_id,
                'event_type' => 'stopped',
                'performed_by_user_id' => $actor?->id,
                'details' => [
                    'reason' => $reason,
                    'stopped_at' => $now->toIso8601String(),
                ],
            ]);

            $this->auditLogger->log(
                'auto_debit.stopped',
                $actor,
                $mandate,
                ['status' => $fromStatus],
                [
                    'status' => $toStatus,
                    'reason' => $reason,
                    'stopped_at' => $now->toIso8601String(),
                ]
            );

            return $mandate;
        });
    }

    protected function updateMandate(AutoDebitMandate $mandate, array $attributes): void
    {
        $mandate->update($attributes);
    }

    protected function updateSubscription(Subscription $subscription, array $attributes): void
    {
        $subscription->update($attributes);
    }

    protected function createAutoDebitEvent(array $attributes): AutoDebitEvent
    {
        return AutoDebitEvent::create($attributes);
    }

    protected function runInTransaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
