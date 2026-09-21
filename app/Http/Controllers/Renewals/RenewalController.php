<?php

namespace App\Http\Controllers\Renewals;

use App\Domain\Authentication\Services\TenantContext;
use App\Domain\Renewals\Actions\RecordRenewalReminderAction;
use App\Domain\Renewals\Renewal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Renewals\RecordReminderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class RenewalController extends Controller
{
    public function index(Request $request, TenantContext $tenant): JsonResponse|Response
    {
        Gate::authorize('viewAny', Renewal::class);

        $query = Renewal::query()->with(['subscription', 'customer', 'partner', 'subPartner']);

        if ($tenant->isAdmin()) {
            // Unrestricted
        } elseif ($tenant->isSubPartner()) {
            $partner = $tenant->partner();
            $query->where('sub_partner_id', $partner?->id);
        } elseif ($tenant->isMainPartner()) {
            // Direct renewals only; access to individual Sub-Partner renewals is blocked pending business confirmation
            $partner = $tenant->partner();
            $query->where('partner_id', $partner?->id)->whereNull('sub_partner_id');
        } else {
            $query->whereRaw('1 = 0');
        }

        $renewals = $query->latest('due_date')->get();

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'data' => $renewals,
            ]);
        }

        return Inertia::render('Renewals/Index', [
            'renewals' => $renewals,
        ]);
    }

    public function show(Renewal $renewal): JsonResponse
    {
        Gate::authorize('view', $renewal);

        $renewal->load(['subscription', 'customer', 'partner', 'subPartner', 'renewedSubscription']);

        return response()->json([
            'data' => $renewal,
        ]);
    }

    public function recordReminder(
        RecordReminderRequest $request,
        Renewal $renewal,
        RecordRenewalReminderAction $action
    ): JsonResponse {
        Gate::authorize('recordReminder', $renewal);

        $updated = $action->execute(
            $renewal,
            $request->validated('milestone'),
            $request->user()
        );

        return response()->json([
            'message' => 'Renewal reminder recorded successfully.',
            'data' => $updated,
        ]);
    }

    public function process(Renewal $renewal): JsonResponse
    {
        Gate::authorize('process', $renewal);

        return response()->json([
            'message' => 'Renewal processing is blocked pending business confirmation.',
        ], 403);
    }
}
