<?php

namespace App\Policies;

use App\Domain\Authentication\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    /**
     * Admin has unrestricted access to view reports per Master §2.
     * Inactive users are strictly blocked from accessing reports.
     */
    public function before(User $user, string $ability): ?bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any reports.
     *
     * Master §2 confirms Admin, Main Partner, and Sub-Partner can view reports.
     *
     * NEEDS BUSINESS CONFIRMATION:
     * - BC-10-01: Report-level data scope per role is not defined in Master §21.
     *   Whether Main Partner or Sub-Partner sees all records, only their own records,
     *   or sub-partner hierarchical records remains unresolved.
     *   Record-level scoping is blocked; this policy authorizes access to the report feature only.
     */
    public function viewAny(User $user): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return $user->isMainPartner() || $user->isSubPartner();
    }
}
