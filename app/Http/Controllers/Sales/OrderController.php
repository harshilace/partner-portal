<?php

namespace App\Http\Controllers\Sales;

use App\Domain\Authentication\Services\TenantContext;
use App\Domain\Payments\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(TenantContext $tenant): JsonResponse
    {
        Gate::authorize('viewAny', Order::class);

        $query = Order::query();

        if ($tenant->isAdmin()) {
            // Unrestricted
        } elseif ($tenant->isSubPartner()) {
            $partner = $tenant->partner();
            $query->where('sub_partner_id', $partner?->id);
        } elseif ($tenant->isMainPartner()) {
            // Direct orders only; access to individual Sub-Partner orders is blocked pending business confirmation
            $partner = $tenant->partner();
            $query->where('partner_id', $partner?->id)->whereNull('sub_partner_id');
        } else {
            $query->whereRaw('1 = 0');
        }

        $orders = $query->with(['customer', 'items.product', 'items.productPlan'])->latest('ordered_at')->get();

        return response()->json([
            'data' => $orders,
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order->load(['customer', 'items.product', 'items.productPlan', 'payments', 'subscriptions']);

        return response()->json([
            'data' => $order,
        ]);
    }
}
