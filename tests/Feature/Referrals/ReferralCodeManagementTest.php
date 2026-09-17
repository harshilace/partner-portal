<?php

namespace Tests\Feature\Referrals;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use App\Domain\Referrals\Actions\CreateReferralCodeAction;
use App\Domain\Referrals\Actions\ToggleReferralCodeAction;
use App\Domain\Referrals\ReferralCode;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use RuntimeException;
use Tests\TestCase;

class ReferralCodeManagementTest extends TestCase
{
    public function test_admin_can_create_referral_code_with_supplied_code(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo('referral_code.created'),
                $this->isInstanceOf(User::class),
                $this->isInstanceOf(ReferralCode::class)
            );

        $action = new class($logger) extends CreateReferralCodeAction
        {
            protected function codeExists(string $code): bool
            {
                return false;
            }

            protected function insertReferralCode(array $attributes): ReferralCode
            {
                return new ReferralCode($attributes);
            }
        };

        $partner = new Partner(['id' => 1, 'status' => 'active']);
        $partner->id = 1;

        $admin = new User(['id' => 10, 'role' => Role::ADMIN->value]);
        $admin->id = 10;

        $referralCode = $action->execute($partner, 'PARTNER123', null, $admin);

        $this->assertEquals('PARTNER123', $referralCode->code);
        $this->assertTrue($referralCode->isActive());
    }

    public function test_create_referral_code_without_code_throws_for_unconfirmed_format(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $action = new CreateReferralCodeAction($logger);

        $partner = new Partner(['id' => 1, 'status' => 'active']);
        $partner->id = 1;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NEEDS BUSINESS CONFIRMATION #1');

        $action->execute($partner, null);
    }

    public function test_create_duplicate_referral_code_throws_domain_exception(): void
    {
        $logger = $this->createMock(AuditLogger::class);

        $action = new class($logger) extends CreateReferralCodeAction
        {
            protected function codeExists(string $code): bool
            {
                return true;
            }
        };

        $partner = new Partner(['id' => 1]);
        $partner->id = 1;

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Referral code already exists.');

        $action->execute($partner, 'EXISTING_CODE');
    }

    public function test_non_admin_cannot_create_or_view_any_referral_codes(): void
    {
        $mainPartnerUser = new User(['id' => 2, 'role' => Role::MAIN_PARTNER->value]);
        $subPartnerUser = new User(['id' => 3, 'role' => Role::SUB_PARTNER->value]);

        $this->assertFalse(Gate::forUser($mainPartnerUser)->allows('create', ReferralCode::class));
        $this->assertFalse(Gate::forUser($mainPartnerUser)->allows('viewAny', ReferralCode::class));
        $this->assertFalse(Gate::forUser($subPartnerUser)->allows('create', ReferralCode::class));
        $this->assertFalse(Gate::forUser($subPartnerUser)->allows('viewAny', ReferralCode::class));
    }

    public function test_toggle_referral_code_action_flips_state_and_audits(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo('referral_code.deactivated'),
                $this->isInstanceOf(User::class),
                $this->isInstanceOf(ReferralCode::class),
                $this->equalTo(['is_active' => true]),
                $this->equalTo(['is_active' => false])
            );

        $action = new class($logger) extends ToggleReferralCodeAction
        {
            protected function updateActiveStatus(ReferralCode $referralCode, bool $isActive): void
            {
                $referralCode->is_active = $isActive;
            }
        };

        $code = new ReferralCode(['code' => 'ACTIVE1', 'is_active' => true]);
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value]);

        $toggled = $action->execute($code, $admin);

        $this->assertFalse($toggled->isActive());
    }
}
