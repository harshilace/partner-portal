<?php

namespace App\Domain\Referrals\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Referrals\ReferralCode;
use Illuminate\Support\Facades\DB;

class ToggleReferralCodeAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Toggle the active state of a referral code.
     * Deletion is not implemented (NEEDS BUSINESS CONFIRMATION #4).
     */
    public function execute(ReferralCode $referralCode, ?User $actor = null): ReferralCode
    {
        return DB::transaction(function () use ($referralCode, $actor) {
            $oldActive = (bool) $referralCode->is_active;
            $newActive = ! $oldActive;

            $this->updateActiveStatus($referralCode, $newActive);

            $event = $newActive ? 'referral_code.activated' : 'referral_code.deactivated';

            $this->auditLogger->log(
                event: $event,
                user: $actor,
                auditable: $referralCode,
                oldValues: ['is_active' => $oldActive],
                newValues: ['is_active' => $newActive]
            );

            return $referralCode;
        });
    }

    /**
     * Update the referral code active state.
     */
    protected function updateActiveStatus(ReferralCode $referralCode, bool $isActive): void
    {
        $referralCode->is_active = $isActive;
        $referralCode->save();
    }
}
