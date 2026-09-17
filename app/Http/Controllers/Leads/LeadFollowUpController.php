<?php

namespace App\Http\Controllers\Leads;

use App\Domain\Leads\Actions\CreateLeadFollowUpAction;
use App\Domain\Leads\Actions\UpdateLeadFollowUpAction;
use App\Domain\Leads\Lead;
use App\Domain\Leads\LeadFollowUp;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leads\CreateLeadFollowUpRequest;
use App\Http\Requests\Leads\UpdateLeadFollowUpRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class LeadFollowUpController extends Controller
{
    /**
     * Store a newly created follow-up for a lead.
     */
    public function store(CreateLeadFollowUpRequest $request, Lead $lead, CreateLeadFollowUpAction $action): JsonResponse
    {
        Gate::authorize('createFollowUp', $lead);

        $followUp = $action->execute(
            lead: $lead,
            data: $request->validated(),
            actor: $request->user()
        );

        return response()->json([
            'message' => 'Lead follow-up created successfully.',
            'data' => $followUp,
        ], 201);
    }

    /**
     * Update the specified lead follow-up.
     */
    public function update(
        UpdateLeadFollowUpRequest $request,
        Lead $lead,
        LeadFollowUp $followUp,
        UpdateLeadFollowUpAction $action
    ): JsonResponse {
        Gate::authorize('updateFollowUp', $lead);

        if ((int) $followUp->lead_id !== (int) $lead->id) {
            abort(404);
        }

        $updated = $action->execute(
            followUp: $followUp,
            data: $request->validated(),
            actor: $request->user()
        );

        return response()->json([
            'message' => 'Lead follow-up updated successfully.',
            'data' => $updated,
        ]);
    }
}
