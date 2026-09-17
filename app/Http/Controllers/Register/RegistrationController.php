<?php

namespace App\Http\Controllers\Register;

use App\Domain\Leads\Actions\RegisterViaReferralAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Register\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    /**
     * Show the public registration form.
     */
    public function show(Request $request): JsonResponse|Response
    {
        $partnerCode = $request->query('partner');

        if ($request->wantsJson()) {
            return response()->json([
                'partner' => $partnerCode,
            ]);
        }

        return Inertia::render('Register', [
            'partner' => $partnerCode,
        ]);
    }

    /**
     * Handle public registration via referral code or unassigned.
     * Blocked by unresolved customer_code / existing-customer rules.
     */
    public function store(RegisterRequest $request, RegisterViaReferralAction $action): JsonResponse
    {
        $partnerCode = $request->input('partner') ?? $request->query('partner');

        $result = $action->execute(
            data: $request->validated(),
            referralCode: $partnerCode
        );

        return response()->json([
            'message' => 'Registration completed successfully.',
            'data' => $result,
        ], 201);
    }
}
