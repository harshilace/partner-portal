<?php

namespace App\Policies;

use App\Domain\Authentication\User;
use App\Domain\Renewals\Renewal;
use Illuminate\Auth\Access\HandlesAuthorization;

class RenewalPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        // Renewal processing is unconfirmed and strictly blocked for all roles
        if ($ability === 'process') {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Renewal $renewal): bool
    {
        $currentPartner = $user->partner();
        if (! $currentPartner) {
            return false;
        }

        if ($user->isSubPartner()) {
            return (int) $renewal->sub_partner_id === (int) $currentPartner->id;
        }

        if ($user->isMainPartner()) {
            // Direct renewals only; access to individual Sub-Partner renewals is blocked pending business confirmation
            return (int) $renewal->partner_id === (int) $currentPartner->id && $renewal->sub_partner_id === null;
        }

        return false;
    }

    /**
     * Renewal processing is strictly blocked pending business confirmation.
     */
    public function process(User $user, Renewal $renewal): bool
    {
        return false;
    }

    /**
     * Record reminder milestone. Exclusive to Admin/system.
     */
    public function recordReminder(User $user, Renewal $renewal): bool
    {
        return false;
    }
}
