<?php

namespace Tests\Feature\Leads;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Leads\Lead;
use App\Domain\Partners\Partner;
use App\Domain\Referrals\ReferralCode;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class LeadDataScopeTest extends TestCase
{
    public function test_sub_partner_a_cannot_view_sub_partner_b_lead(): void
    {
        $subPartnerA = new Partner(['id' => 10, 'type' => 'sub']);
        $subPartnerA->id = 10;

        $userA = new class(['id' => 100, 'role' => Role::SUB_PARTNER->value]) extends User
        {
            public ?Partner $mockPartner = null;

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $userA->mockPartner = $subPartnerA;

        $leadB = new Lead(['id' => 1, 'sub_partner_id' => 20]);

        $this->assertFalse(Gate::forUser($userA)->allows('view', $leadB));
    }

    public function test_main_partner_a_cannot_view_main_partner_b_lead(): void
    {
        $mainPartnerA = new Partner(['id' => 10, 'type' => 'main']);
        $mainPartnerA->id = 10;

        $userA = new class(['id' => 100, 'role' => Role::MAIN_PARTNER->value]) extends User
        {
            public ?Partner $mockPartner = null;

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $userA->mockPartner = $mainPartnerA;

        $leadB = new Lead(['id' => 1, 'partner_id' => 20]);

        $this->assertFalse(Gate::forUser($userA)->allows('view', $leadB));
    }

    public function test_main_partner_cannot_view_sub_partner_individual_lead(): void
    {
        $mainPartner = new Partner(['id' => 10, 'type' => 'main']);
        $mainPartner->id = 10;

        $user = new class(['id' => 100, 'role' => Role::MAIN_PARTNER->value]) extends User
        {
            public ?Partner $mockPartner = null;

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $user->mockPartner = $mainPartner;

        // Lead with sub_partner_id = 15, partner_id null or different
        $subPartnerLead = new Lead(['id' => 1, 'partner_id' => null, 'sub_partner_id' => 15]);

        $this->assertFalse(Gate::forUser($user)->allows('view', $subPartnerLead));
    }

    public function test_referral_code_policy_enforces_partner_scoping(): void
    {
        $mainPartner = new Partner(['id' => 10, 'type' => 'main']);
        $mainPartner->id = 10;

        $user = new class(['id' => 100, 'role' => Role::MAIN_PARTNER->value]) extends User
        {
            public ?Partner $mockPartner = null;

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $user->mockPartner = $mainPartner;

        $ownCode = new ReferralCode(['id' => 1, 'partner_id' => 10]);
        $otherCode = new ReferralCode(['id' => 2, 'partner_id' => 20]);

        $this->assertTrue(Gate::forUser($user)->allows('view', $ownCode));
        $this->assertFalse(Gate::forUser($user)->allows('view', $otherCode));
    }
}
