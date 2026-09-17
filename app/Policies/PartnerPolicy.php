<?php

namespace App\Policies;

use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartnerPolicy
{
    use HandlesAuthorization;

    /**
     * Admin has unrestricted access to partner abilities.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view the partner record.
     */
    public function view(User $user, Partner $partner): bool
    {
        $currentPartner = $user->partner();
        if (! $currentPartner) {
            return false;
        }

        // Exact match: view own partner record
        if ($currentPartner->id === $partner->id) {
            return true;
        }

        // Main Partner can view their own sub-partners
        if ($user->isMainPartner()) {
            if ($partner->parent_partner_id === $currentPartner->id) {
                return true;
            }

            return $currentPartner->subPartners()->where('id', $partner->id)->exists();
        }

        // Sub-Partner cannot view any other partner
        return false;
    }

    /**
     * Determine whether the user can create a Sub-Partner.
     * Admin and Main Partner are authorized; Sub-Partner is strictly denied.
     */
    public function createSubPartner(User $user, ?Partner $parentPartner = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isMainPartner()) {
            if ($parentPartner === null) {
                return true;
            }

            return (int) $parentPartner->id === (int) $user->partner()?->id;
        }

        // Sub-Partner CANNOT create Sub-Partners (Section 4, 5)
        return false;
    }

    /**
     * Determine whether the user can update the partner record.
     */
    public function update(User $user, Partner $partner): bool
    {
        $currentPartner = $user->partner();
        if (! $currentPartner) {
            return false;
        }

        if ($currentPartner->id === $partner->id) {
            return true;
        }

        if ($user->isMainPartner() && $partner->parent_partner_id === $currentPartner->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the partner.
     * Exclusive to Admin.
     */
    public function delete(User $user, Partner $partner): bool
    {
        return false;
    }
}
