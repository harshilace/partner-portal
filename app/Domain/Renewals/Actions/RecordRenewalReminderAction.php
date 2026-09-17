<?php

namespace App\Domain\Renewals\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Renewals\Renewal;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordRenewalReminderAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Record a renewal reminder milestone timestamp (30d, 15d, 7d, 1d).
     */
    public function execute(Renewal $renewal, string $milestone, ?User $actor = null): Renewal
    {
        $validMilestones = Renewal::validMilestones();
        if (! in_array($milestone, $validMilestones, true)) {
            throw new InvalidArgumentException("Invalid renewal reminder milestone: [{$milestone}]. Expected one of: ".implode(', ', $validMilestones));
        }

        $column = "reminder_{$milestone}_sent_at";

        if (! empty($renewal->{$column})) {
            throw new DomainException("Renewal reminder [{$milestone}] has already been recorded.");
        }

        return $this->runInTransaction(function () use ($renewal, $column, $milestone, $actor) {
            $now = Carbon::now();

            $this->updateRenewal($renewal, [
                $column => $now,
            ]);

            $this->auditLogger->log(
                'renewal.reminder_recorded',
                $actor,
                $renewal,
                null,
                [
                    'milestone' => $milestone,
                    'sent_at' => $now->toIso8601String(),
                ]
            );

            return $renewal;
        });
    }

    protected function updateRenewal(Renewal $renewal, array $attributes): void
    {
        $renewal->update($attributes);
    }

    protected function runInTransaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
