<?php

namespace Tests\Feature\Referrals;

use App\Domain\Partners\Partner;
use App\Domain\Referrals\Actions\ResolveReferralCodeAction;
use App\Domain\Referrals\ReferralCode;
use RuntimeException;
use Tests\TestCase;

class ReferralCodeResolutionTest extends TestCase
{
    public function test_empty_or_null_code_returns_null_for_unassigned_flow(): void
    {
        $action = new ResolveReferralCodeAction;

        $this->assertNull($action->execute(null));
        $this->assertNull($action->execute(''));
        $this->assertNull($action->execute('   '));
    }

    public function test_inactive_or_missing_code_throws_for_unconfirmed_behavior(): void
    {
        $action = new class extends ResolveReferralCodeAction
        {
            protected function findActiveReferralCode(string $code): ?ReferralCode
            {
                return null;
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NEEDS BUSINESS CONFIRMATION #5');

        $action->execute('INVALID_CODE');
    }

    public function test_code_belonging_to_deactivated_partner_throws_for_unconfirmed_behavior(): void
    {
        $action = new class extends ResolveReferralCodeAction
        {
            protected function findActiveReferralCode(string $code): ?ReferralCode
            {
                $codeModel = new ReferralCode(['code' => $code, 'is_active' => true, 'partner_id' => 1]);
                $codeModel->setRelation('partner', new Partner(['id' => 1, 'status' => 'inactive']));

                return $codeModel;
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NEEDS BUSINESS CONFIRMATION #6');

        $action->execute('INACTIVE_PARTNER_CODE');
    }

    public function test_code_with_sub_partner_throws_for_unconfirmed_sub_partner_active_check(): void
    {
        $action = new class extends ResolveReferralCodeAction
        {
            protected function findActiveReferralCode(string $code): ?ReferralCode
            {
                $codeModel = new ReferralCode([
                    'code' => $code,
                    'is_active' => true,
                    'partner_id' => 1,
                    'sub_partner_id' => 2,
                ]);
                $codeModel->setRelation('partner', new Partner(['id' => 1, 'status' => 'active']));
                $codeModel->setRelation('subPartner', new Partner(['id' => 2, 'status' => 'active']));

                return $codeModel;
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NEEDS BUSINESS CONFIRMATION #7');

        $action->execute('SUB_PARTNER_CODE');
    }

    public function test_active_code_with_active_main_partner_and_no_sub_partner_resolves(): void
    {
        $action = new class extends ResolveReferralCodeAction
        {
            protected function findActiveReferralCode(string $code): ?ReferralCode
            {
                $codeModel = new ReferralCode([
                    'code' => $code,
                    'is_active' => true,
                    'partner_id' => 1,
                    'sub_partner_id' => null,
                ]);
                $codeModel->setRelation('partner', new Partner(['id' => 1, 'status' => 'active']));

                return $codeModel;
            }
        };

        $resolved = $action->execute('VALID_CODE');

        $this->assertNotNull($resolved);
        $this->assertEquals('VALID_CODE', $resolved->code);
    }
}
