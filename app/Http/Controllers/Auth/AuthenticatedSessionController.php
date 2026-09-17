<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Authentication\Actions\AuthenticateUserAction;
use App\Domain\Authentication\Actions\LogoutUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, AuthenticateUserAction $action): JsonResponse|RedirectResponse
    {
        $user = $request->authenticate($action);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Authenticated successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
            ]);
        }

        return redirect()->intended('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request, LogoutUserAction $action): JsonResponse|RedirectResponse
    {
        $action->execute($request);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Logged out successfully.',
            ]);
        }

        return redirect('/');
    }
}
