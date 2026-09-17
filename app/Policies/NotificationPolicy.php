<?php

namespace App\Policies;

use App\Domain\Authentication\User;

class NotificationPolicy
{
    /**
     * Determine whether the user can view the notification.
     */
    public function view(User $user, mixed $notification): bool
    {
        return $this->isOwner($user, $notification);
    }

    /**
     * Determine whether the user can update (mark as read) the notification.
     */
    public function update(User $user, mixed $notification): bool
    {
        return $this->isOwner($user, $notification);
    }

    /**
     * Verify strict ownership to prevent IDOR attacks.
     */
    protected function isOwner(User $user, mixed $notification): bool
    {
        if (! isset($notification->notifiable_id)) {
            return false;
        }

        if ((int) $notification->notifiable_id !== (int) $user->id) {
            return false;
        }

        if (isset($notification->notifiable_type) && ! in_array($notification->notifiable_type, [$user->getMorphClass(), User::class, 'App\\Domain\\Authentication\\User', 'App\\Models\\User'], true)) {
            return false;
        }

        return true;
    }
}
