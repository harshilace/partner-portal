<?php

namespace App\Policies;

use App\Domain\Authentication\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuditPolicy
{
    use HandlesAuthorization;

    /**
     * Admin has confirmed access to view audit history per Master §2.
     * Inactive users are strictly blocked.
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
     * Determine whether the user can view any audit logs.
     *
     * Master §2 confirms Admin may view audit history.
     * Main Partner and Sub-Partner access is not defined in the Master.
     *
     * TEMPORARY SECURITY DEFAULT:
     * Pending resolution of BC-11-01, non-Admin users are denied by default.
     * This is a technical security safeguard and NOT a confirmed business requirement.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }
}
