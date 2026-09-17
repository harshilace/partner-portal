<?php

namespace App\Policies;

use App\Domain\Authentication\User;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriptionPolicy
{
    use HandlesAuthorization;

    /**
     * Admin has unrestricted authority over subscriptions.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can view the subscription snapshot.
     */
    public function view(User $user, Subscription $subscription): bool
    {
        $currentPartner = $user->partner();
        if (! $currentPartner) {
            return false;
        }

        if ($user->isSubPartner()) {
            return (int) $subscription->sub_partner_id === (int) $currentPartner->id;
        }

        if ($user->isMainPartner()) {
            // Direct subscriptions only; access to individual Sub-Partner subscriptions is blocked pending business confirmation
            return (int) $subscription->partner_id === (int) $currentPartner->id && $subscription->sub_partner_id === null;
        }

        return false;
    }

    /**
     * Cancel a subscription. Exclusive to Admin (Section 4, 18).
     * Non-admins are strictly unauthorized.
     */
    public function cancel(User $user, Subscription $subscription): bool
    {
        return false;
    }

    /**
     * Stop auto-debit for a subscription. Exclusive to Admin (Section 4, 18).
     * Non-admins are strictly unauthorized.
     */
    public function stopAutoDebit(User $user, Subscription $subscription): bool
    {
        return false;
    }
}
