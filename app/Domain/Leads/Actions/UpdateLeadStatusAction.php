<?php

namespace App\Domain\Leads\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Leads\Enums\LeadStatus;
use App\Domain\Leads\Lead;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateLeadStatusAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Update lead status and record status history.
     * Blocked because transition rules are unconfirmed (NEEDS BUSINESS CONFIRMATION #12).
     *
     * @throws RuntimeException
     */
    public function execute(
        Lead $lead,
        LeadStatus|string $status,
        ?User $actor = null,
        ?string $remarks = null
    ): Lead {
        return DB::transaction(function () {
            throw new RuntimeException(
                'Lead status transition rules are not confirmed — NEEDS BUSINESS CONFIRMATION #12'
            );
        });
    }
}
