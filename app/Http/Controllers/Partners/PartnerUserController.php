<?php

namespace App\Http\Controllers\Partners;

use App\Domain\Authentication\User;
use App\Domain\Partners\Actions\AssignUserToPartnerAction;
use App\Domain\Partners\Actions\RemoveUserFromPartnerAction;
use App\Domain\Partners\Partner;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partners\AssignUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerUserController extends Controller
{
    /**
     * Assign a user to a partner.
     * Admin-only authority pending confirmation (NEEDS BUSINESS CONFIRMATION #11).
     */
    public function store(
        AssignUserRequest $request,
        Partner $partner,
        AssignUserToPartnerAction $action
    ): JsonResponse {
        $user = User::findOrFail($request->validated('user_id'));

        $partnerUser = $action->execute(
            partner: $partner,
            user: $user,
            actor: $request->user()
        );

        return response()->json([
            'message' => 'User assigned to partner successfully.',
            'data' => $partnerUser,
        ], 201);
    }

    /**
     * Remove a user from a partner.
     * Admin-only authority pending confirmation (NEEDS BUSINESS CONFIRMATION #11).
     */
    public function destroy(
        Request $request,
        Partner $partner,
        User $user,
        RemoveUserFromPartnerAction $action
    ): JsonResponse {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'This action is unauthorized.');
        }

        $action->execute(
            partner: $partner,
            user: $user,
            actor: $request->user()
        );

        return response()->json([
            'message' => 'User removed from partner successfully.',
        ]);
    }
}
