<?php

namespace App\Domain\Partners\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use RuntimeException;

class CreateSubPartnerAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Execute sub-partner creation.
     * Blocked because partner_code generation policy is unresolved (NEEDS BUSINESS CONFIRMATION #1).
     *
     * @param  array<string, mixed>  $data
     *
     * @throws RuntimeException
     */
    public function execute(array $data, Partner $parentPartner, ?User $actor = null): Partner
    {
        throw new RuntimeException(
            'Sub-Partner creation is blocked: partner_code generation policy is not yet confirmed (NEEDS BUSINESS CONFIRMATION #1).'
        );
    }
}
