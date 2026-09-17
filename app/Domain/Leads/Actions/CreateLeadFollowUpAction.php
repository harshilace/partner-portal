<?php

namespace App\Domain\Leads\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Leads\Lead;
use App\Domain\Leads\LeadFollowUp;
use Illuminate\Support\Facades\DB;

class CreateLeadFollowUpAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Create a follow-up for a lead.
     * Follow-up status defaults to and is fixed at 'pending' (NEEDS BUSINESS CONFIRMATION #14).
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(Lead $lead, array $data, ?User $actor = null): LeadFollowUp
    {
        return DB::transaction(function () use ($lead, $data, $actor) {
            $followUp = $this->insertFollowUp([
                'lead_id' => $lead->id,
                'user_id' => $actor?->id,
                'follow_up_at' => $data['follow_up_at'],
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            $this->auditLogger->log(
                event: 'lead.follow_up_created',
                user: $actor,
                auditable: $lead,
                newValues: [
                    'lead_id' => $lead->id,
                    'user_id' => $actor?->id,
                    'follow_up_at' => $data['follow_up_at'],
                    'status' => 'pending',
                    'notes' => $data['notes'] ?? null,
                ]
            );

            return $followUp;
        });
    }

    /**
     * Insert a lead follow-up record.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function insertFollowUp(array $attributes): LeadFollowUp
    {
        return LeadFollowUp::create($attributes);
    }
}
