<?php

namespace Tests\Feature\Partners;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Actions\AssignUserToPartnerAction;
use App\Domain\Partners\Actions\RemoveUserFromPartnerAction;
use App\Domain\Partners\Actions\UpdatePartnerAction;
use App\Domain\Partners\Actions\UpdateSubPartnerAction;
use App\Domain\Partners\Partner;
use App\Domain\Partners\PartnerUser;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PartnerAuditTest extends TestCase
{
    public function test_update_partner_action_logs_audit_event(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo('partner.updated'),
                $this->isInstanceOf(User::class),
                $this->isInstanceOf(Partner::class),
                $this->equalTo(['name' => 'Original Name']),
                $this->equalTo(['name' => 'Renamed Main Partner'])
            );

        $action = new UpdatePartnerAction($logger);

        $partner = new class(['name' => 'Original Name']) extends Partner
        {
            public function update(array $attributes = [], array $options = [])
            {
                $this->fill($attributes);

                return true;
            }
        };

        $actor = new User(['id' => 1, 'role' => Role::ADMIN->value]);

        $action->execute($partner, ['name' => 'Renamed Main Partner'], $actor);
    }

    public function test_update_sub_partner_action_logs_audit_event(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo('partner.updated'),
                $this->isInstanceOf(User::class),
                $this->isInstanceOf(Partner::class),
                $this->equalTo(['name' => 'Original Sub Name']),
                $this->equalTo(['name' => 'Renamed Sub Partner'])
            );

        $action = new UpdateSubPartnerAction($logger);

        $subPartner = new class(['name' => 'Original Sub Name']) extends Partner
        {
            public function update(array $attributes = [], array $options = [])
            {
                $this->fill($attributes);

                return true;
            }
        };

        $actor = new User(['id' => 2, 'role' => Role::MAIN_PARTNER->value]);

        $action->execute($subPartner, ['name' => 'Renamed Sub Partner'], $actor);
    }

    public function test_assign_user_logs_audit_event(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo('partner.user_assigned'),
                $this->isInstanceOf(User::class),
                $this->isInstanceOf(Partner::class),
                $this->isNull(),
                $this->equalTo(['partner_id' => 10, 'user_id' => 50])
            );

        $action = new class($logger) extends AssignUserToPartnerAction
        {
            protected function assignmentExists(int|string $partnerId, int|string $userId): bool
            {
                return false;
            }

            protected function createAssignment(int|string $partnerId, int|string $userId): PartnerUser
            {
                return new PartnerUser(['partner_id' => $partnerId, 'user_id' => $userId]);
            }
        };

        $partner = new Partner(['id' => 10]);
        $partner->id = 10;

        $user = new User(['id' => 50, 'role' => Role::PARTNER_USER->value]);
        $user->id = 50;

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value]);

        $action->execute($partner, $user, $admin);
    }

    public function test_remove_user_logs_audit_event(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo('partner.user_removed'),
                $this->isInstanceOf(User::class),
                $this->isInstanceOf(Partner::class),
                $this->equalTo(['partner_id' => 10, 'user_id' => 50]),
                $this->isNull()
            );

        $action = new class($logger) extends RemoveUserFromPartnerAction
        {
            protected function findAssignment(int|string $partnerId, int|string $userId): ?PartnerUser
            {
                return new PartnerUser(['partner_id' => $partnerId, 'user_id' => $userId]);
            }

            protected function deleteAssignment(PartnerUser $partnerUser): bool
            {
                return true;
            }
        };

        $partner = new Partner(['id' => 10]);
        $partner->id = 10;

        $user = new User(['id' => 50, 'role' => Role::PARTNER_USER->value]);
        $user->id = 50;

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value]);

        $action->execute($partner, $user, $admin);
    }
}
