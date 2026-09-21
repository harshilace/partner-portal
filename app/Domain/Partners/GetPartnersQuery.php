<?php

namespace App\Domain\Partners;

use App\Domain\Authentication\Services\TenantContext;
use Illuminate\Support\Collection;

class GetPartnersQuery
{
    /**
     * Retrieve partners scoped to the authenticated tenant context.
     *
     * @return Collection<int, Partner>
     */
    public function execute(TenantContext $tenant): Collection
    {
        if ($tenant->isAdmin()) {
            return Partner::all();
        }

        if ($tenant->isMainPartner()) {
            $mainPartner = $tenant->partner();

            return Partner::where('id', $mainPartner?->id)
                ->orWhere('parent_partner_id', $mainPartner?->id)
                ->get();
        }

        if ($tenant->isSubPartner()) {
            $subPartner = $tenant->partner();

            return Partner::where('id', $subPartner?->id)->get();
        }

        return collect();
    }
}
