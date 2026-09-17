<?php

namespace Tests\Feature\Leads;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Leads\Actions\CreateLeadFollowUpAction;
use App\Domain\Leads\Actions\UpdateLeadFollowUpAction;
use App\Domain\Leads\Lead;
use App\Domain\Leads\LeadFollowUp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class LeadFollowUpTest extends TestCase
{
    public function test_create_lead_follow_up_action_sets_pending_status_and_audits(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo('lead.follow_up_created'),
                $this->isInstanceOf(User::class),
                $this->isInstanceOf(Lead::class)
            );

        $action = new class($logger) extends CreateLeadFollowUpAction
        {
            protected function insertFollowUp(array $attributes): LeadFollowUp
            {
                return new LeadFollowUp($attributes);
            }
        };

        $lead = new Lead(['id' => 1]);
        $lead->id = 1;

        $admin = new User(['id' => 10, 'role' => Role::ADMIN->value]);
        $admin->id = 10;

        $followUp = $action->execute($lead, [
            'follow_up_at' => '2026-10-01 10:00:00',
            'notes' => 'Call back next week',
        ], $admin);

        $this->assertEquals('pending', $followUp->status);
        $this->assertEquals(10, $followUp->user_id);
    }

    public function test_update_lead_follow_up_action_ignores_status_and_only_updates_allowed_fields(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo('lead.follow_up_updated'),
                $this->isInstanceOf(User::class),
                $this->anything(),
                $this->equalTo(['follow_up_at' => '2026-10-01 10:00:00', 'notes' => 'Old notes']),
                $this->equalTo(['follow_up_at' => '2026-10-05 14:00:00', 'notes' => 'New notes'])
            );

        $action = new class($logger) extends UpdateLeadFollowUpAction
        {
            protected function saveFollowUp(LeadFollowUp $followUp, array $attributes): void
            {
                $followUp->fill($attributes);
            }
        };

        $lead = new Lead(['id' => 10]);
        $lead->id = 10;

        $followUp = new LeadFollowUp([
            'id' => 1,
            'lead_id' => 10,
            'status' => 'pending',
            'follow_up_at' => '2026-10-01 10:00:00',
            'notes' => 'Old notes',
        ]);
        $followUp->id = 1;
        $followUp->setRelation('lead', $lead);

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value]);
        $admin->id = 1;

        // Attempting to pass status => 'completed' must be ignored
        $action->execute($followUp, [
            'follow_up_at' => '2026-10-05 14:00:00',
            'notes' => 'New notes',
            'status' => 'completed',
        ], $admin);

        $this->assertEquals('pending', $followUp->status);
        $this->assertEquals('New notes', $followUp->notes);
    }

    public function test_non_admin_cannot_create_or_update_lead_follow_ups(): void
    {
        $mainUser = new User(['id' => 2, 'role' => Role::MAIN_PARTNER->value]);
        $subUser = new User(['id' => 3, 'role' => Role::SUB_PARTNER->value]);
        $lead = new Lead(['id' => 1]);

        $this->assertFalse(Gate::forUser($mainUser)->allows('createFollowUp', $lead));
        $this->assertFalse(Gate::forUser($mainUser)->allows('updateFollowUp', $lead));
        $this->assertFalse(Gate::forUser($subUser)->allows('createFollowUp', $lead));
        $this->assertFalse(Gate::forUser($subUser)->allows('updateFollowUp', $lead));
    }

    public function test_admin_can_create_and_update_follow_ups(): void
    {
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value]);
        $lead = new Lead(['id' => 1]);

        $this->assertTrue(Gate::forUser($admin)->allows('createFollowUp', $lead));
        $this->assertTrue(Gate::forUser($admin)->allows('updateFollowUp', $lead));
    }
}
