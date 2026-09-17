<?php

namespace App\Domain\Dashboard;

use App\Domain\Authentication\User;
use Illuminate\Auth\Access\AuthorizationException;

class AdminDashboardQuery
{
    /**
     * Execute the query for the Admin dashboard shell.
     *
     * @return array{role: string, data: object, pending_confirmation: array<int, string>}
     *
     * @throws AuthorizationException
     */
    public function execute(User $user): array
    {
        if (! $user->isAdmin()) {
            throw new AuthorizationException('User is not authorized to access the admin dashboard.');
        }

        return [
            'role' => 'admin',
            'data' => (object) [],
            'pending_confirmation' => [
                'metrics',
                'date_filters',
                'charts',
            ],
        ];
    }
}
