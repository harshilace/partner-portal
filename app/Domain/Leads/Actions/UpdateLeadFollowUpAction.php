<?php

namespace App\Domain\Leads\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Leads\LeadFollowUp;
use Illuminate\Support\Facades\DB;

class UpdateLeadFollowUpAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Update a lead follow-up.
     * Only follow_up_at and notes are updatable. Status updates are blocked (NEEDS BUSINESS CONFIRMATION #14).
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(LeadFollowUp $followUp, array $data, ?User $actor = null): LeadFollowUp
    {
        return DB::transaction(function () use ($followUp, $data, $actor) {
            $filteredData = array_intersect_key($data, array_flip(['follow_up_at', 'notes']));

            $oldValues = [];
            foreach (array_keys($filteredData) as $key) {
                $oldValues[$key] = $followUp->{$key};
            }

            $this->saveFollowUp($followUp, $filteredData);

            $auditable = $followUp->relationLoaded('lead') ? $followUp->lead : $followUp;

            $this->auditLogger->log(
                event: 'lead.follow_up_updated',
                user: $actor,
                auditable: $auditable,
                oldValues: $oldValues,
                newValues: $filteredData
            );

            return $followUp;
        });
    }

    /**
     * Save the updated lead follow-up attributes.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function saveFollowUp(LeadFollowUp $followUp, array $attributes): void
    {
        $followUp->update($attributes);
    }
}
