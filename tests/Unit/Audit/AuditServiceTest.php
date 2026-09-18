<?php

namespace Tests\Unit\Audit;

use App\Domain\Audit\Services\AuditService;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditServiceTest extends TestCase
{
    /**
     * AuditService index returns 501 JsonResponse.
     */
    public function test_index_returns_501_json_response(): void
    {
        $user = new User([
            'id' => 1,
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $service = new AuditService;
        $response = $service->index($user);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(501, $response->getStatusCode());
    }

    /**
     * AuditService index executes zero database queries.
     */
    public function test_index_executes_zero_database_queries(): void
    {
        DB::enableQueryLog();

        $user = new User([
            'id' => 1,
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $service = new AuditService;
        $service->index($user);

        $this->assertEmpty(DB::getQueryLog(), 'AuditService::index must not execute any database queries.');
    }

    /**
     * AuditService index response contains blocked_by references for BC-11-01 through BC-11-04.
     */
    public function test_index_response_contains_blocked_by_bc_references(): void
    {
        $user = new User([
            'id' => 1,
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $service = new AuditService;
        $response = $service->index($user);
        $data = $response->getData(true);

        $this->assertArrayHasKey('blocked_by', $data);
        $this->assertSame([
            'BC-11-01',
            'BC-11-02',
            'BC-11-03',
            'BC-11-04',
        ], $data['blocked_by']);
    }
}
