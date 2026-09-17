<?php

namespace App\Http\Controllers\Leads;

use App\Domain\Authentication\Services\TenantContext;
use App\Domain\Leads\Actions\UpdateLeadStatusAction;
use App\Domain\Leads\Lead;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leads\UpdateLeadStatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LeadController extends Controller
{
    /**
     * Display a listing of leads scoped to the tenant context.
     */
    public function index(Request $request, TenantContext $tenant): JsonResponse
    {
        Gate::authorize('viewAny', Lead::class);

        $query = $tenant->scopeQuery(Lead::query());

        $leads = $query->with(['partner', 'subPartner', 'customer'])->get();

        return response()->json([
            'data' => $leads,
        ]);
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead): JsonResponse
    {
        Gate::authorize('view', $lead);

        $lead->load(['partner', 'subPartner', 'customer', 'statusHistories', 'followUps']);

        return response()->json([
            'data' => $lead,
        ]);
    }

    /**
     * Update the status of the specified lead.
     * Blocked pending lead status transition confirmation (NEEDS BUSINESS CONFIRMATION #12).
     */
    public function update(UpdateLeadStatusRequest $request, Lead $lead, UpdateLeadStatusAction $action): JsonResponse
    {
        Gate::authorize('update', $lead);

        $updated = $action->execute(
            lead: $lead,
            status: $request->validated('status'),
            actor: $request->user(),
            remarks: $request->validated('remarks')
        );

        return response()->json([
            'message' => 'Lead status updated successfully.',
            'data' => $updated,
        ]);
    }
}
