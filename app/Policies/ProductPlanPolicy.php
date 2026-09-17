<?php

namespace App\Policies;

use App\Domain\Authentication\User;
use App\Domain\Products\ProductPlan;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPlanPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($ability === 'delete') {
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

    public function view(User $user, ProductPlan $plan): bool
    {
        return $user->isActive();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ProductPlan $plan): bool
    {
        return false;
    }

    public function delete(User $user, ProductPlan $plan): bool
    {
        return false;
    }
}
