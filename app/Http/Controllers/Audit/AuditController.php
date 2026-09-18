<?php

namespace App\Http\Controllers\Audit;

use App\Domain\Audit\AuditEntry;
use App\Domain\Audit\Services\AuditService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditController extends Controller
{
    /**
     * Display audit history.
     */
    public function index(Request $request, AuditService $service): JsonResponse
    {
        Gate::authorize('viewAny', AuditEntry::class);

        return $service->index($request->user(), $request->all());
    }
}
