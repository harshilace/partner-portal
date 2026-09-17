<?php

namespace Tests\Feature\Partners;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Actions\AssignUserToPartnerAction;
use App\Domain\Partners\Actions\RemoveUserFromPartnerAction;
use App\Domain\Partners\Partner;
use App\Domain\Partners\PartnerUser;
use App\Http\Requests\Partners\AssignUserRequest;
use DomainException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PartnerUserAssignmentTest extends TestCase
{
    public function test_admin_can_assign_user_to_partner_and_never_modifies_user_role(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                'partner.user_assigned',
                $this->isInstanceOf(User::class),
                $this->isInstanceOf(Partner::class),
                null,
                ['partner_id' => 10, 'user_id' => 20]
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

        $user = new User(['id' => 20, 'role' => Role::PARTNER_USER->value]);
        $user->id = 20;

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value]);

        $partnerUser = $action->execute($partner, $user, $admin);

        $this->assertEquals(10, $partnerUser->partner_id);
        $this->assertEquals(20, $partnerUser->user_id);
        // Explicit requirement: users.role MUST NOT be modified
        $this->assertEquals(Role::PARTNER_USER->value, $user->role);
    }

    public function test_duplicate_user_assignment_throws_domain_exception(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $action = new class($logger) extends AssignUserToPartnerAction
        {
            protected function assignmentExists(int|string $partnerId, int|string $userId): bool
            {
                return true; // Simulate already assigned
            }
        };

        $partner = new Partner(['id' => 10]);
        $partner->id = 10;

        $user = new User(['id' => 20]);
        $user->id = 20;

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User is already assigned to this partner.');

        $action->execute($partner, $user);
    }

    public function test_admin_can_remove_user_from_partner_without_deleting_user(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                'partner.user_removed',
                $this->isInstanceOf(User::class),
                $this->isInstanceOf(Partner::class),
                ['partner_id' => 10, 'user_id' => 20]
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

        $user = new User(['id' => 20, 'role' => Role::PARTNER_USER->value]);
        $user->id = 20;

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value]);

        $result = $action->execute($partner, $user, $admin);

        $this->assertTrue($result);
        // Explicit requirement: user record itself is preserved
        $this->assertEquals(20, $user->id);
    }

    public function test_removing_unassigned_user_throws_domain_exception(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $action = new class($logger) extends RemoveUserFromPartnerAction
        {
            protected function findAssignment(int|string $partnerId, int|string $userId): ?PartnerUser
            {
                return null;
            }
        };

        $partner = new Partner(['id' => 10]);
        $partner->id = 10;

        $user = new User(['id' => 20]);
        $user->id = 20;

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User is not assigned to this partner.');

        $action->execute($partner, $user);
    }

    public function test_assign_user_request_authorizes_admin_and_denies_others(): void
    {
        $admin = new User(['role' => Role::ADMIN->value]);
        $mainPartner = new User(['role' => Role::MAIN_PARTNER->value]);
        $subPartner = new User(['role' => Role::SUB_PARTNER->value]);

        $request = new AssignUserRequest;

        $request->setUserResolver(fn () => $admin);
        $this->assertTrue($request->authorize());

        $request->setUserResolver(fn () => $mainPartner);
        $this->assertFalse($request->authorize());

        $request->setUserResolver(fn () => $subPartner);
        $this->assertFalse($request->authorize());
    }
}
