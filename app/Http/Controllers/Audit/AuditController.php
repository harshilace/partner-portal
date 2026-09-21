<?php

namespace App\Http\Controllers\Audit;

use App\Domain\Audit\AuditEntry;
use App\Domain\Audit\Services\AuditService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AuditController extends Controller
{
    /**
     * Display audit history.
     */
    public function index(Request $request, AuditService $service): JsonResponse|Response
    {
        Gate::authorize('viewAny', AuditEntry::class);

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return $service->index($request->user(), $request->all());
        }

        return Inertia::render('Audit/Index');
    }
}
