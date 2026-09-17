<?php

namespace App\Domain\Partners\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use RuntimeException;

class DeactivatePartnerAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Execute partner deactivation.
     * Blocked because exact deactivated status string value is unresolved (NEEDS BUSINESS CONFIRMATION #5).
     * Partner records must never be deleted (Section 4, 12).
     *
     * @throws RuntimeException
     */
    public function execute(Partner $partner, ?User $actor = null): Partner
    {
        throw new RuntimeException(
            'Partner deactivation is blocked: deactivated status string value is not yet confirmed (NEEDS BUSINESS CONFIRMATION #5).'
        );
    }
}
