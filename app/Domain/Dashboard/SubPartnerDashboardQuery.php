<?php

namespace App\Domain\Dashboard;

use App\Domain\Authentication\User;
use Illuminate\Auth\Access\AuthorizationException;

class SubPartnerDashboardQuery
{
    /**
     * Execute the query for the Sub-Partner dashboard shell.
     *
     * @return array{role: string, data: object, pending_confirmation: array<int, string>}
     *
     * @throws AuthorizationException
     */
    public function execute(User $user): array
    {
        if (! $user->isSubPartner()) {
            throw new AuthorizationException('User is not authorized to access the sub-partner dashboard.');
        }

        $partner = $user->partner();
        if (! $partner) {
            throw new AuthorizationException('No active sub-partner organization associated with user.');
        }

        // Scope strictly to authenticated sub-partner ID — never user-submitted parameter
        $subPartnerId = $partner->id;

        return [
            'role' => 'sub_partner',
            'data' => (object) [],
            'pending_confirmation' => [
                'metrics',
                'date_filters',
                'charts',
            ],
        ];
    }
}
