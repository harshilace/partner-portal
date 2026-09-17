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
            if ((int) $partner->parent_partner_id === (int) $currentPartner->id) {
                return true;
            }

            if ($currentPartner->relationLoaded('subPartners')) {
                return $currentPartner->subPartners->contains('id', $partner->id);
            }

            if (! $currentPartner->exists) {
                return false;
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
     * Determine whether the user can view any partner records.
     * List scoping is enforced server-side via TenantContext.
     */
    public function viewAny(User $user): bool
    {
        return $user->isMainPartner() || $user->isSubPartner();
    }

    /**
     * Determine whether the user can deactivate the partner.
     * Admin is handled via before().
     * Main Partner deactivation authority is blocked pending confirmation (NEEDS BUSINESS CONFIRMATION #8).
     * Sub-Partner is strictly denied.
     */
    public function deactivate(User $user, Partner $partner): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the partner.
     * Partner records must never be deleted (Section 4, 12).
     */
    public function delete(User $user, Partner $partner): bool
    {
        return false;
    }
}
