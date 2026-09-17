<?php

namespace App\Http\Controllers\Subscriptions;

use App\Domain\Authentication\Services\TenantContext;
use App\Domain\Subscriptions\Actions\StopAutoDebitAction;
use App\Domain\Subscriptions\AutoDebitMandate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subscriptions\StopAutoDebitRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AutoDebitController extends Controller
{
    public function index(TenantContext $tenant): JsonResponse
    {
        Gate::authorize('viewAny', AutoDebitMandate::class);

        $query = AutoDebitMandate::query()->with(['subscription', 'customer']);

        if ($tenant->isAdmin()) {
            // Unrestricted
        } elseif ($tenant->isSubPartner()) {
            $partner = $tenant->partner();
            $query->whereHas('subscription', function ($q) use ($partner) {
                $q->where('sub_partner_id', $partner?->id);
            });
        } elseif ($tenant->isMainPartner()) {
            // Direct subscriptions only; access to individual Sub-Partner mandates is blocked pending business confirmation
            $partner = $tenant->partner();
            $query->whereHas('subscription', function ($q) use ($partner) {
                $q->where('partner_id', $partner?->id)->whereNull('sub_partner_id');
            });
        } else {
            $query->whereRaw('1 = 0');
        }

        $mandates = $query->latest()->get();

        return response()->json([
            'data' => $mandates,
        ]);
    }

    public function show(AutoDebitMandate $mandate): JsonResponse
    {
        Gate::authorize('view', $mandate);

        $mandate->load(['subscription', 'customer', 'stoppedByUser', 'events.performedByUser']);

        return response()->json([
            'data' => $mandate,
        ]);
    }

    public function stop(
        StopAutoDebitRequest $request,
        AutoDebitMandate $mandate,
        StopAutoDebitAction $action
    ): JsonResponse {
        Gate::authorize('stop', $mandate);

        $updated = $action->execute(
            $mandate,
            $request->validated('reason'),
            $request->user()
        );

        return response()->json([
            'message' => 'Auto-debit mandate stopped successfully.',
            'data' => $updated,
        ]);
    }
}
