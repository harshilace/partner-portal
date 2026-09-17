<?php

namespace App\Domain\Referrals\Actions;

use App\Domain\Partners\Partner;
use App\Domain\Referrals\ReferralCode;
use RuntimeException;

class ResolveReferralCodeAction
{
    /**
     * Resolve a referral code from a URL parameter.
     *
     * @throws RuntimeException
     */
    public function execute(?string $code): ?ReferralCode
    {
        if ($code === null || trim($code) === '') {
            return null;
        }

        $trimmedCode = trim($code);

        $referralCode = $this->findActiveReferralCode($trimmedCode);

        if (! $referralCode) {
            throw new RuntimeException(
                'Behavior when referral code is inactive or not found is not confirmed — NEEDS BUSINESS CONFIRMATION #5'
            );
        }

        $partner = $referralCode->partner ?? $this->findPartner($referralCode->partner_id);

        if (! $partner || $partner->status !== 'active') {
            throw new RuntimeException(
                'Behavior when referral code belongs to a deactivated partner is not confirmed — NEEDS BUSINESS CONFIRMATION #6'
            );
        }

        if ($referralCode->sub_partner_id !== null) {
            throw new RuntimeException(
                'Whether sub-partner must be active for referral acceptance is not confirmed — NEEDS BUSINESS CONFIRMATION #7'
            );
        }

        return $referralCode;
    }

    /**
     * Find an active referral code by code string.
     */
    protected function findActiveReferralCode(string $code): ?ReferralCode
    {
        return ReferralCode::where('code', $code)
            ->where('is_active', true)
            ->with(['partner', 'subPartner'])
            ->first();
    }

    /**
     * Find partner by ID if not eager loaded.
     */
    protected function findPartner(int|string|null $partnerId): ?Partner
    {
        if (! $partnerId) {
            return null;
        }

        return Partner::find($partnerId);
    }
}
