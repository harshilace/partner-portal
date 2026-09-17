<?php

namespace App\Policies;

use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    /**
     * Admin has unrestricted access to customers.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any customers.
     * Scoped server-side via TenantContext.
     */
    public function viewAny(User $user): bool
    {
        return $user->isMainPartner() || $user->isSubPartner();
    }

    /**
     * Determine whether the user can view the specific customer.
     * Sub-Partner: own assigned customers only (current_sub_partner_id matches own partner id).
     * Main Partner: own direct customers only (current_partner_id matches own partner id AND current_sub_partner_id is null).
     * Sub-partner individual customer view is BLOCKED pending confirmation (NEEDS BUSINESS CONFIRMATION #9).
     * Historical partner customer profile view is BLOCKED pending confirmation (NEEDS BUSINESS CONFIRMATION #10).
     */
    public function view(User $user, Customer $customer): bool
    {
        $currentPartner = $user->partner();
        if (! $currentPartner) {
            return false;
        }

        if ($user->isSubPartner()) {
            return (int) $customer->current_sub_partner_id === (int) $currentPartner->id;
        }

        if ($user->isMainPartner()) {
            return (int) $customer->current_partner_id === (int) $currentPartner->id
                && $customer->current_sub_partner_id === null;
        }

        return false;
    }

    /**
     * Determine whether the user can change the customer's partner mapping.
     * Admin only (handled via before).
     */
    public function changeMapping(User $user, Customer $customer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can manually create a customer.
     * Blocked for all non-Admin pending confirmation (NEEDS BUSINESS CONFIRMATION #2).
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can edit a customer profile.
     * Blocked for all non-Admin pending confirmation (NEEDS BUSINESS CONFIRMATION #3).
     */
    public function update(User $user, Customer $customer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the customer.
     * Customers must never be deleted (ON DELETE RESTRICT).
     */
    public function delete(User $user, Customer $customer): bool
    {
        return false;
    }
}
