<?php

namespace App\Policies;

use App\Domain\Authentication\User;
use App\Domain\Payments\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if (in_array($ability, ['update', 'delete'], true)) {
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

    public function view(User $user, Order $order): bool
    {
        $currentPartner = $user->partner();
        if (! $currentPartner) {
            return false;
        }

        if ($user->isSubPartner()) {
            return (int) $order->sub_partner_id === (int) $currentPartner->id;
        }

        if ($user->isMainPartner()) {
            // Direct orders only; access to individual Sub-Partner orders is blocked pending business confirmation
            return (int) $order->partner_id === (int) $currentPartner->id && $order->sub_partner_id === null;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Order $order): bool
    {
        return false;
    }

    public function delete(User $user, Order $order): bool
    {
        return false;
    }
}
