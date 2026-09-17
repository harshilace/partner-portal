<?php

namespace App\Policies;

use App\Domain\Authentication\User;
use App\Domain\Leads\Lead;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
{
    use HandlesAuthorization;

    /**
     * Admin has unrestricted access to leads.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any leads.
     * List scoping is enforced server-side via TenantContext.
     */
    public function viewAny(User $user): bool
    {
        return $user->isMainPartner() || $user->isSubPartner();
    }

    /**
     * Determine whether the user can view the specific lead.
     * Sub-partner: own leads only (sub_partner_id matches own partner id).
     * Main partner: own leads only (partner_id matches own partner id).
     * Main Partner access to sub-partner leads is BLOCKED pending confirmation (NEEDS BUSINESS CONFIRMATION #16).
     */
    public function view(User $user, Lead $lead): bool
    {
        $currentPartner = $user->partner();
        if (! $currentPartner) {
            return false;
        }

        if ($user->isSubPartner()) {
            return (int) $lead->sub_partner_id === (int) $currentPartner->id;
        }

        if ($user->isMainPartner()) {
            return (int) $lead->partner_id === (int) $currentPartner->id;
        }

        return false;
    }

    /**
     * Determine whether the user can manually create a lead.
     * Blocked for all non-Admin pending confirmation (NEEDS BUSINESS CONFIRMATION #18).
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update lead status.
     * Blocked for all non-Admin pending confirmation (NEEDS BUSINESS CONFIRMATION #13).
     */
    public function update(User $user, Lead $lead): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create a lead follow-up.
     * Blocked for all non-Admin pending confirmation (NEEDS BUSINESS CONFIRMATION #15).
     */
    public function createFollowUp(User $user, Lead $lead): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update a lead follow-up.
     * Blocked for all non-Admin pending confirmation (NEEDS BUSINESS CONFIRMATION #15).
     */
    public function updateFollowUp(User $user, Lead $lead): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the lead.
     * Leads must never be deleted (Section 12, Phase 1 schema ON DELETE RESTRICT).
     */
    public function delete(User $user, Lead $lead): bool
    {
        return false;
    }
}
