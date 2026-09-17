<?php

namespace App\Http\Controllers\ReferralCodes;

use App\Domain\Partners\Partner;
use App\Domain\Referrals\Actions\CreateReferralCodeAction;
use App\Domain\Referrals\Actions\ToggleReferralCodeAction;
use App\Domain\Referrals\ReferralCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReferralCodes\CreateReferralCodeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReferralCodeController extends Controller
{
    /**
     * Display a listing of referral codes.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', ReferralCode::class);

        $referralCodes = ReferralCode::with(['partner', 'subPartner'])->get();

        return response()->json([
            'data' => $referralCodes,
        ]);
    }

    /**
     * Store a newly created referral code.
     */
    public function store(CreateReferralCodeRequest $request, CreateReferralCodeAction $action): JsonResponse
    {
        Gate::authorize('create', ReferralCode::class);

        $partner = Partner::findOrFail($request->validated('partner_id'));
        $subPartner = $request->validated('sub_partner_id')
            ? Partner::findOrFail($request->validated('sub_partner_id'))
            : null;

        $referralCode = $action->execute(
            partner: $partner,
            code: $request->validated('code'),
            subPartner: $subPartner,
            actor: $request->user()
        );

        return response()->json([
            'message' => 'Referral code created successfully.',
            'data' => $referralCode,
        ], 201);
    }

    /**
     * Display the specified referral code.
     */
    public function show(ReferralCode $referralCode): JsonResponse
    {
        Gate::authorize('view', $referralCode);

        $referralCode->load(['partner', 'subPartner']);

        return response()->json([
            'data' => $referralCode,
        ]);
    }

    /**
     * Toggle active status of the specified referral code.
     */
    public function update(ReferralCode $referralCode, ToggleReferralCodeAction $action): JsonResponse
    {
        Gate::authorize('update', $referralCode);

        $updated = $action->execute(
            referralCode: $referralCode,
            actor: request()->user()
        );

        return response()->json([
            'message' => 'Referral code status updated successfully.',
            'data' => $updated,
        ]);
    }
}
