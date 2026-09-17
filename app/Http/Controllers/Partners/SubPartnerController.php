<?php

namespace App\Http\Controllers\Partners;

use App\Domain\Partners\Actions\CreateSubPartnerAction;
use App\Domain\Partners\Actions\DeactivatePartnerAction;
use App\Domain\Partners\Actions\UpdateSubPartnerAction;
use App\Domain\Partners\Partner;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partners\CreateSubPartnerRequest;
use App\Http\Requests\Partners\UpdatePartnerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class SubPartnerController extends Controller
{
    /**
     * Display a listing of sub-partners under the specified parent partner.
     */
    public function index(Partner $partner): JsonResponse
    {
        Gate::authorize('view', $partner);

        return response()->json([
            'data' => $partner->subPartners,
        ]);
    }

    /**
     * Store a newly created sub-partner under the specified parent partner.
     * Blocked pending partner_code confirmation (NEEDS BUSINESS CONFIRMATION #1).
     */
    public function store(CreateSubPartnerRequest $request, Partner $partner, CreateSubPartnerAction $action): JsonResponse
    {
        $subPartner = $action->execute(
            data: $request->validated(),
            parentPartner: $partner,
            actor: $request->user()
        );

        return response()->json([
            'message' => 'Sub-Partner created successfully.',
            'data' => $subPartner,
        ], 201);
    }

    /**
     * Display the specified sub-partner under the parent partner.
     */
    public function show(Partner $partner, Partner $subPartner): JsonResponse
    {
        if ((int) $subPartner->parent_partner_id !== (int) $partner->id) {
            abort(404, 'Sub-Partner not found under this partner.');
        }

        Gate::authorize('view', $subPartner);

        return response()->json([
            'data' => $subPartner,
        ]);
    }

    /**
     * Update the specified sub-partner under the parent partner.
     */
    public function update(
        UpdatePartnerRequest $request,
        Partner $partner,
        Partner $subPartner,
        UpdateSubPartnerAction $action
    ): JsonResponse {
        if ((int) $subPartner->parent_partner_id !== (int) $partner->id) {
            abort(404, 'Sub-Partner not found under this partner.');
        }

        Gate::authorize('update', $subPartner);

        $updated = $action->execute(
            subPartner: $subPartner,
            data: $request->validated(),
            actor: $request->user()
        );

        return response()->json([
            'message' => 'Sub-Partner updated successfully.',
            'data' => $updated,
        ]);
    }

    /**
     * Deactivate the specified sub-partner under the parent partner.
     * Blocked pending deactivated status confirmation (NEEDS BUSINESS CONFIRMATION #5).
     */
    public function destroy(Partner $partner, Partner $subPartner, DeactivatePartnerAction $action): JsonResponse
    {
        if ((int) $subPartner->parent_partner_id !== (int) $partner->id) {
            abort(404, 'Sub-Partner not found under this partner.');
        }

        Gate::authorize('deactivate', $subPartner);

        $action->execute(
            partner: $subPartner,
            actor: request()->user()
        );

        return response()->json([
            'message' => 'Sub-Partner deactivated successfully.',
        ]);
    }
}
