<?php

namespace App\Domain\Audit\Services;

use App\Domain\Authentication\User;
use Illuminate\Http\JsonResponse;

class AuditService
{
    /**
     * Retrieve audit log entries.
     *
     * BLOCKED: Returns 501 pending resolution of business confirmations:
     * - BC-11-01: Main Partner / Sub-Partner audit log visibility & role data scope
     * - BC-11-02: Audit log UI columns / field exposure
     * - BC-11-03: Filter and search behavior
     * - BC-11-04: Pagination behavior
     */
    public function index(User $user, array $filters = []): JsonResponse
    {
        return response()->json([
            'message' => 'Audit log retrieval is not yet implemented.',
            'blocked_by' => [
                'BC-11-01',
                'BC-11-02',
                'BC-11-03',
                'BC-11-04',
            ],
        ], 501);
    }
}
