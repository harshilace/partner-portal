<?php

namespace Tests\Feature\Renewals;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Renewals\Actions\RecordRenewalReminderAction;
use App\Domain\Renewals\Renewal;
use DomainException;
use InvalidArgumentException;
use Tests\TestCase;

class RenewalReminderMilestonesTest extends TestCase
{
    public function test_can_record_the_four_documented_reminder_milestones(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->exactly(4))
            ->method('log')
            ->with($this->equalTo('renewal.reminder_recorded'));

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $action = new class($logger) extends RecordRenewalReminderAction
        {
            protected function runInTransaction(callable $callback): mixed
            {
                return $callback();
            }

            protected function updateRenewal(Renewal $renewal, array $attributes): void
            {
                $renewal->forceFill($attributes);
            }
        };

        $milestones = ['30d', '15d', '7d', '1d'];

        foreach ($milestones as $milestone) {
            $renewal = new Renewal([
                'id' => 10,
                'due_date' => '2026-10-30',
                'status' => 'pending',
            ]);

            $result = $action->execute($renewal, $milestone, $admin);

            $column = "reminder_{$milestone}_sent_at";
            $this->assertNotNull($result->{$column});
            $this->assertTrue($result->hasReminderSent($milestone));
        }
    }

    public function test_recording_duplicate_milestone_throws_domain_exception(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $renewal = new Renewal([
            'id' => 10,
            'status' => 'pending',
            'reminder_15d_sent_at' => now(),
        ]);

        $action = new RecordRenewalReminderAction($logger);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Renewal reminder [15d] has already been recorded.');

        $action->execute($renewal, '15d', $admin);
    }

    public function test_recording_invalid_milestone_throws_invalid_argument_exception(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $renewal = new Renewal([
            'id' => 10,
            'status' => 'pending',
        ]);

        $action = new RecordRenewalReminderAction($logger);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid renewal reminder milestone: [60d]. Expected one of: 30d, 15d, 7d, 1d');

        $action->execute($renewal, '60d', $admin);
    }
}
