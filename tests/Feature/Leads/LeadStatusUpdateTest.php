<?php

namespace Tests\Feature\Leads;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Leads\Actions\UpdateLeadStatusAction;
use App\Domain\Leads\Enums\LeadStatus;
use App\Domain\Leads\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use RuntimeException;
use Tests\TestCase;

class LeadStatusUpdateTest extends TestCase
{
    public function test_update_lead_status_action_always_throws_for_unconfirmed_transitions(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $action = new UpdateLeadStatusAction($logger);

        $lead = new Lead(['id' => 1, 'status' => LeadStatus::NEW->value]);
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NEEDS BUSINESS CONFIRMATION #12');

        $action->execute($lead, LeadStatus::CONTACTED, $admin);
    }

    public function test_non_admin_cannot_update_lead_status(): void
    {
        $mainUser = new User(['id' => 2, 'role' => Role::MAIN_PARTNER->value]);
        $subUser = new User(['id' => 3, 'role' => Role::SUB_PARTNER->value]);
        $lead = new Lead(['id' => 1]);

        $this->assertFalse(Gate::forUser($mainUser)->allows('update', $lead));
        $this->assertFalse(Gate::forUser($subUser)->allows('update', $lead));
    }

    public function test_admin_is_authorized_for_lead_update_via_policy_before(): void
    {
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value]);
        $lead = new Lead(['id' => 1]);

        $this->assertTrue(Gate::forUser($admin)->allows('update', $lead));
    }
}
