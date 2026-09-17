<?php

namespace App\Http\Controllers\Customers;

use App\Domain\Authentication\Services\TenantContext;
use App\Domain\Customers\Actions\ChangeCustomerPartnerMappingAction;
use App\Domain\Customers\Customer;
use App\Domain\Partners\Partner;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\ChangeCustomerPartnerMappingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers scoped to tenant context.
     */
    public function index(Request $request, TenantContext $tenant): JsonResponse
    {
        Gate::authorize('viewAny', Customer::class);

        $query = Customer::query();

        if ($tenant->isAdmin()) {
            // Unrestricted
        } elseif ($tenant->isSubPartner()) {
            $partner = $tenant->partner();
            $query->where('current_sub_partner_id', $partner?->id);
        } elseif ($tenant->isMainPartner()) {
            // Direct customers only; sub-partner customer access blocked pending confirmation (#9)
            $partner = $tenant->partner();
            $query->where('current_partner_id', $partner?->id)->whereNull('current_sub_partner_id');
        } else {
            $query->whereRaw('1 = 0');
        }

        $customers = $query->with(['currentPartner', 'currentSubPartner'])->get();

        return response()->json([
            'data' => $customers,
        ]);
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): JsonResponse
    {
        Gate::authorize('view', $customer);

        $customer->load(['currentPartner', 'currentSubPartner', 'attributions']);

        return response()->json([
            'data' => $customer,
        ]);
    }

    /**
     * Update the customer's partner mapping.
     * Admin only.
     */
    public function updateMapping(
        ChangeCustomerPartnerMappingRequest $request,
        Customer $customer,
        ChangeCustomerPartnerMappingAction $action
    ): JsonResponse {
        Gate::authorize('changeMapping', $customer);

        $newPartner = Partner::findOrFail($request->validated('partner_id'));
        $newSubPartner = $request->validated('sub_partner_id')
            ? Partner::findOrFail($request->validated('sub_partner_id'))
            : null;

        $updated = $action->execute(
            customer: $customer,
            newPartner: $newPartner,
            newSubPartner: $newSubPartner,
            actor: $request->user()
        );

        return response()->json([
            'message' => 'Customer partner mapping updated successfully.',
            'data' => $updated,
        ]);
    }
}
