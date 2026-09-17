<?php

namespace App\Policies;

use App\Domain\Authentication\User;
use App\Domain\Subscriptions\AutoDebitMandate;
use Illuminate\Auth\Access\HandlesAuthorization;

class AutoDebitMandatePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        // Mandate creation is unconfirmed and strictly blocked for all roles
        if ($ability === 'create') {
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

    public function view(User $user, AutoDebitMandate $mandate): bool
    {
        $currentPartner = $user->partner();
        if (! $currentPartner) {
            return false;
        }

        $subscription = $mandate->subscription;
        if (! $subscription) {
            return false;
        }

        if ($user->isSubPartner()) {
            return (int) $subscription->sub_partner_id === (int) $currentPartner->id;
        }

        if ($user->isMainPartner()) {
            // Direct subscriptions only; access to individual Sub-Partner mandates is blocked pending business confirmation
            return (int) $subscription->partner_id === (int) $currentPartner->id && $subscription->sub_partner_id === null;
        }

        return false;
    }

    /**
     * Stop auto-debit for a mandate. Exclusive to Admin (Master Section 4, 18).
     * Non-admins are strictly unauthorized.
     */
    public function stop(User $user, AutoDebitMandate $mandate): bool
    {
        return false;
    }

    /**
     * Mandate creation is blocked pending business confirmation.
     */
    public function create(User $user): bool
    {
        return false;
    }
}
