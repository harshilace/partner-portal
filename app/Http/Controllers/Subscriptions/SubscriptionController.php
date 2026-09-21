<?php

namespace App\Http\Controllers\Subscriptions;

use App\Domain\Authentication\Services\TenantContext;
use App\Domain\Subscriptions\Actions\CancelSubscriptionAction;
use App\Domain\Subscriptions\Subscription;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subscriptions\CancelSubscriptionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(Request $request, TenantContext $tenant): JsonResponse|Response
    {
        Gate::authorize('viewAny', Subscription::class);

        $query = Subscription::query();

        if ($tenant->isAdmin()) {
            // Unrestricted
        } elseif ($tenant->isSubPartner()) {
            $partner = $tenant->partner();
            $query->where('sub_partner_id', $partner?->id);
        } elseif ($tenant->isMainPartner()) {
            // Direct subscriptions only; access to individual Sub-Partner subscriptions is blocked pending business confirmation
            $partner = $tenant->partner();
            $query->where('partner_id', $partner?->id)->whereNull('sub_partner_id');
        } else {
            $query->whereRaw('1 = 0');
        }

        $subscriptions = $query->with(['customer', 'product', 'productPlan'])->latest()->get();

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'data' => $subscriptions,
            ]);
        }

        return Inertia::render('Subscriptions/Index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    public function show(Subscription $subscription): JsonResponse
    {
        Gate::authorize('view', $subscription);

        $subscription->load(['customer', 'product', 'productPlan', 'statusHistories.changedByUser']);

        return response()->json([
            'data' => $subscription,
        ]);
    }

    public function cancel(
        CancelSubscriptionRequest $request,
        Subscription $subscription,
        CancelSubscriptionAction $action
    ): JsonResponse {
        Gate::authorize('cancel', $subscription);

        $updated = $action->execute(
            $subscription,
            $request->validated('reason'),
            $request->user()
        );

        return response()->json([
            'message' => 'Subscription cancelled successfully.',
            'data' => $updated,
        ]);
    }
}
