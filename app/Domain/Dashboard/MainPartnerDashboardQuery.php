<?php

namespace App\Domain\Dashboard;

use App\Domain\Authentication\User;
use Illuminate\Auth\Access\AuthorizationException;

class MainPartnerDashboardQuery
{
    /**
     * Execute the query for the Main Partner dashboard shell.
     *
     * @return array{role: string, data: object, pending_confirmation: array<int, string>}
     *
     * @throws AuthorizationException
     */
    public function execute(User $user): array
    {
        if (! $user->isMainPartner()) {
            throw new AuthorizationException('User is not authorized to access the main partner dashboard.');
        }

        $partner = $user->partner();
        if (! $partner) {
            throw new AuthorizationException('No active main partner organization associated with user.');
        }

        // Scope strictly to authenticated main partner ID — never user-submitted parameter
        $mainPartnerId = $partner->id;

        return [
            'role' => 'main_partner',
            'data' => (object) [],
            'pending_confirmation' => [
                'metrics',
                'date_filters',
                'charts',
            ],
        ];
    }
}
