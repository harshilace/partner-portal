<?php

namespace Tests\Feature\Leads;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Leads\Lead;
use App\Domain\Partners\Partner;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class LeadPolicyTest extends TestCase
{
    public function test_admin_has_unrestricted_lead_access_via_before(): void
    {
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value]);
        $lead = new Lead(['id' => 10, 'partner_id' => 999]);

        $this->assertTrue(Gate::forUser($admin)->allows('view', $lead));
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Lead::class));
    }

    public function test_sub_partner_can_view_own_lead_only(): void
    {
        $subPartner = new Partner(['id' => 5, 'type' => 'sub']);
        $subPartner->id = 5;

        $user = new class(['id' => 2, 'role' => Role::SUB_PARTNER->value]) extends User
        {
            public ?Partner $mockPartner = null;

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $user->mockPartner = $subPartner;

        $ownLead = new Lead(['id' => 1, 'sub_partner_id' => 5]);
        $otherSubLead = new Lead(['id' => 2, 'sub_partner_id' => 6]);

        $this->assertTrue(Gate::forUser($user)->allows('view', $ownLead));
        $this->assertFalse(Gate::forUser($user)->allows('view', $otherSubLead));
    }

    public function test_main_partner_can_view_own_lead(): void
    {
        $mainPartner = new Partner(['id' => 10, 'type' => 'main']);
        $mainPartner->id = 10;

        $user = new class(['id' => 3, 'role' => Role::MAIN_PARTNER->value]) extends User
        {
            public ?Partner $mockPartner = null;

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $user->mockPartner = $mainPartner;

        $ownLead = new Lead(['id' => 1, 'partner_id' => 10]);
        $otherMainLead = new Lead(['id' => 2, 'partner_id' => 20]);

        $this->assertTrue(Gate::forUser($user)->allows('view', $ownLead));
        $this->assertFalse(Gate::forUser($user)->allows('view', $otherMainLead));
    }

    public function test_main_partner_cannot_view_sub_partner_leads_pending_confirmation(): void
    {
        $mainPartner = new Partner(['id' => 10, 'type' => 'main']);
        $mainPartner->id = 10;

        $user = new class(['id' => 3, 'role' => Role::MAIN_PARTNER->value]) extends User
        {
            public ?Partner $mockPartner = null;

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $user->mockPartner = $mainPartner;

        // Lead belonging to sub-partner (sub_partner_id = 50, but partner_id is different or not own)
        $subPartnerLead = new Lead(['id' => 3, 'partner_id' => null, 'sub_partner_id' => 50]);

        $this->assertFalse(Gate::forUser($user)->allows('view', $subPartnerLead));
    }
}
