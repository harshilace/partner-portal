<?php

namespace App\Http\Controllers\Sales;

use App\Domain\Authentication\Services\TenantContext;
use App\Domain\Customers\Customer;
use App\Domain\Leads\Lead;
use App\Domain\Payments\Actions\ProcessSaleAction;
use App\Domain\Products\ProductPlan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\ProcessSaleRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class SaleController extends Controller
{
    /**
     * Process sale transaction according to Master Section 14 & 17.
     */
    public function store(
        ProcessSaleRequest $request,
        ProcessSaleAction $action,
        TenantContext $tenant
    ): JsonResponse {
        $customer = Customer::findOrFail($request->validated('customer_id'));
        $plan = ProductPlan::with('product')->findOrFail($request->validated('product_plan_id'));

        $lead = $request->validated('lead_id')
            ? Lead::findOrFail($request->validated('lead_id'))
            : null;

        // Verify tenant scope authorization
        if (! $tenant->isAdmin()) {
            $partner = $tenant->partner();
            if (! $partner) {
                throw new AuthorizationException('User has no associated partner.');
            }

            if ($tenant->isSubPartner()) {
                if ((int) $customer->current_sub_partner_id !== (int) $partner->id) {
                    throw new AuthorizationException('Cannot process sale for customer outside sub-partner scope.');
                }
                if ($lead && (int) $lead->sub_partner_id !== (int) $partner->id) {
                    throw new AuthorizationException('Cannot process sale for lead outside sub-partner scope.');
                }
            } elseif ($tenant->isMainPartner()) {
                if ((int) $customer->current_partner_id !== (int) $partner->id || $customer->current_sub_partner_id !== null) {
                    throw new AuthorizationException('Cannot process sale for customer outside main partner scope.');
                }
                if ($lead && ((int) $lead->partner_id !== (int) $partner->id || $lead->sub_partner_id !== null)) {
                    throw new AuthorizationException('Cannot process sale for lead outside main partner scope.');
                }
            }
        }

        $result = $action->execute([
            'customer' => $customer,
            'plan' => $plan,
            'lead' => $lead,
            'quantity' => $request->validated('quantity', 1),
            'payment_method' => $request->validated('payment_method'),
            'payment_options' => $request->validated('payment_options', []),
            'starts_at' => $request->validated('starts_at'),
            'expires_at' => $request->validated('expires_at'),
            'discounts' => $request->input('discounts'),
            'custom_pricing' => $request->input('custom_pricing'),
            'commercial_rules' => $request->input('commercial_rules'),
        ], $request->user());

        return response()->json([
            'message' => 'Sale processed successfully.',
            'data' => $result,
        ], 201);
    }
}
