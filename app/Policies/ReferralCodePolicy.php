<?php

namespace App\Policies;

use App\Domain\Authentication\User;
use App\Domain\Referrals\ReferralCode;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReferralCodePolicy
{
    use HandlesAuthorization;

    /**
     * Admin has unrestricted access to referral codes.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any referral codes.
     * Non-Admin cannot view general referral code listings.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the specific referral code.
     * Main Partner: own partner_id codes only.
     * Sub-Partner: own sub_partner_id codes only.
     */
    public function view(User $user, ReferralCode $code): bool
    {
        $currentPartner = $user->partner();
        if (! $currentPartner) {
            return false;
        }

        if ($user->isMainPartner()) {
            return (int) $code->partner_id === (int) $currentPartner->id;
        }

        if ($user->isSubPartner()) {
            return (int) $code->sub_partner_id === (int) $currentPartner->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create a referral code.
     * Admin only (handled via before).
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update/toggle a referral code.
     * Admin only (handled via before).
     */
    public function update(User $user, ReferralCode $code): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete a referral code.
     * Referral code deletion is not implemented (NEEDS BUSINESS CONFIRMATION #4).
     */
    public function delete(User $user, ReferralCode $code): bool
    {
        return false;
    }
}
