<?php

namespace App\Domain\Renewals\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Renewals\Renewal;
use App\Domain\Subscriptions\Subscription;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class CreateRenewalAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Create a renewal record snapshotting historical partner ownership from the subscription.
     */
    public function execute(Subscription $subscription, CarbonInterface|string $dueDate, ?User $actor = null): Renewal
    {
        return $this->runInTransaction(function () use ($subscription, $dueDate, $actor) {
            $formattedDueDate = $dueDate instanceof CarbonInterface ? $dueDate->toDateString() : (string) $dueDate;

            $renewal = $this->createRenewal([
                'subscription_id' => $subscription->id,
                'customer_id' => $subscription->customer_id,
                'partner_id' => $subscription->partner_id,
                'sub_partner_id' => $subscription->sub_partner_id,
                'due_date' => $formattedDueDate,
                'status' => 'pending',
            ]);

            $this->auditLogger->log(
                'renewal.created',
                $actor,
                $renewal,
                null,
                [
                    'subscription_id' => $subscription->id,
                    'due_date' => $formattedDueDate,
                    'partner_id' => $subscription->partner_id,
                    'sub_partner_id' => $subscription->sub_partner_id,
                ]
            );

            return $renewal;
        });
    }

    protected function createRenewal(array $attributes): Renewal
    {
        return Renewal::create($attributes);
    }

    protected function runInTransaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
