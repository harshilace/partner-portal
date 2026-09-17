<?php

namespace App\Http\Controllers\Dashboard;

use App\Domain\Dashboard\AdminDashboardQuery;
use App\Domain\Dashboard\MainPartnerDashboardQuery;
use App\Domain\Dashboard\SubPartnerDashboardQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected AdminDashboardQuery $adminQuery,
        protected MainPartnerDashboardQuery $mainPartnerQuery,
        protected SubPartnerDashboardQuery $subPartnerQuery
    ) {}

    /**
     * Display the role-aware dashboard shell.
     */
    public function index(Request $request): JsonResponse|Response
    {
        Gate::authorize('viewDashboard');

        $user = $request->user();

        $result = match (true) {
            $user->isAdmin() => $this->adminQuery->execute($user),
            $user->isMainPartner() => $this->mainPartnerQuery->execute($user),
            $user->isSubPartner() => $this->subPartnerQuery->execute($user),
            default => abort(403, 'Unauthorized dashboard access.'),
        };

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json($result);
        }

        return Inertia::render('Dashboard/Index', $result);
    }
}
