<?php

namespace App\Http\Controllers\Partners;

use App\Domain\Authentication\Services\TenantContext;
use App\Domain\Partners\Actions\CreatePartnerAction;
use App\Domain\Partners\Actions\DeactivatePartnerAction;
use App\Domain\Partners\Actions\UpdatePartnerAction;
use App\Domain\Partners\Partner;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partners\CreatePartnerRequest;
use App\Http\Requests\Partners\UpdatePartnerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PartnerController extends Controller
{
    /**
     * Display a listing of the partners scoped to the tenant context.
     */
    public function index(Request $request, TenantContext $tenant): JsonResponse
    {
        Gate::authorize('viewAny', Partner::class);

        if ($tenant->isAdmin()) {
            $partners = Partner::all();
        } elseif ($tenant->isMainPartner()) {
            $mainPartner = $tenant->partner();
            $partners = Partner::where('id', $mainPartner?->id)
                ->orWhere('parent_partner_id', $mainPartner?->id)
                ->get();
        } elseif ($tenant->isSubPartner()) {
            $subPartner = $tenant->partner();
            $partners = Partner::where('id', $subPartner?->id)->get();
        } else {
            $partners = collect();
        }

        return response()->json([
            'data' => $partners,
        ]);
    }

    /**
     * Store a newly created partner.
     * Blocked pending partner_code confirmation (NEEDS BUSINESS CONFIRMATION #1).
     */
    public function store(CreatePartnerRequest $request, CreatePartnerAction $action): JsonResponse
    {
        $partner = $action->execute(
            data: $request->validated(),
            actor: $request->user()
        );

        return response()->json([
            'message' => 'Partner created successfully.',
            'data' => $partner,
        ], 201);
    }

    /**
     * Display the specified partner.
     */
    public function show(Partner $partner): JsonResponse
    {
        Gate::authorize('view', $partner);

        return response()->json([
            'data' => $partner,
        ]);
    }

    /**
     * Update the specified partner.
     */
    public function update(UpdatePartnerRequest $request, Partner $partner, UpdatePartnerAction $action): JsonResponse
    {
        $updatedPartner = $action->execute(
            partner: $partner,
            data: $request->validated(),
            actor: $request->user()
        );

        return response()->json([
            'message' => 'Partner updated successfully.',
            'data' => $updatedPartner,
        ]);
    }

    /**
     * Deactivate the specified partner.
     * Blocked pending deactivated status confirmation (NEEDS BUSINESS CONFIRMATION #5).
     */
    public function destroy(Partner $partner, DeactivatePartnerAction $action): JsonResponse
    {
        Gate::authorize('deactivate', $partner);

        $action->execute(
            partner: $partner,
            actor: request()->user()
        );

        return response()->json([
            'message' => 'Partner deactivated successfully.',
        ]);
    }
}
