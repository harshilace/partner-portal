<?php

namespace Tests\Feature\Partners;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\Services\TenantContext;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use App\Policies\PartnerPolicy;
use Tests\TestCase;

class PartnerDataScopeTest extends TestCase
{
    protected PartnerPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PartnerPolicy;
    }

    public function test_sub_partner_cannot_view_foreign_sub_partner(): void
    {
        $subPartnerA = new Partner(['id' => 101, 'type' => 'sub', 'parent_partner_id' => 10]);
        $subPartnerA->id = 101;

        $subPartnerB = new Partner(['id' => 102, 'type' => 'sub', 'parent_partner_id' => 10]);
        $subPartnerB->id = 102;

        $userA = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $userA->setRelation('partners', collect([$subPartnerA]));

        $tenantA = new TenantContext($userA);

        // Can access self
        $this->assertTrue($this->policy->view($userA, $subPartnerA));
        $this->assertTrue($tenantA->canAccessPartner($subPartnerA));

        // IDOR protection: cannot view or access foreign sub-partner
        $this->assertFalse($this->policy->view($userA, $subPartnerB));
        $this->assertFalse($tenantA->canAccessPartner($subPartnerB));
    }

    public function test_main_partner_cannot_view_sub_partner_of_foreign_main_partner(): void
    {
        $mainPartnerA = new Partner(['id' => 10, 'type' => 'main']);
        $mainPartnerA->id = 10;

        $ownSubPartner = new Partner(['id' => 101, 'type' => 'sub', 'parent_partner_id' => 10]);
        $ownSubPartner->id = 101;

        $foreignMainPartner = new Partner(['id' => 20, 'type' => 'main']);
        $foreignMainPartner->id = 20;

        $foreignSubPartner = new Partner(['id' => 201, 'type' => 'sub', 'parent_partner_id' => 20]);
        $foreignSubPartner->id = 201;

        $userA = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $userA->setRelation('partners', collect([$mainPartnerA]));

        $tenantA = new TenantContext($userA);

        // Can access own partner and own sub-partner
        $this->assertTrue($this->policy->view($userA, $mainPartnerA));
        $this->assertTrue($this->policy->view($userA, $ownSubPartner));
        $this->assertTrue($tenantA->canAccessPartner($mainPartnerA));
        $this->assertTrue($tenantA->canAccessPartner($ownSubPartner));

        // IDOR protection: cannot access foreign main partner or their sub-partners
        $this->assertFalse($this->policy->view($userA, $foreignMainPartner));
        $this->assertFalse($this->policy->view($userA, $foreignSubPartner));
        $this->assertFalse($tenantA->canAccessPartner($foreignMainPartner));
        $this->assertFalse($tenantA->canAccessPartner($foreignSubPartner));
    }

    public function test_admin_has_unrestricted_partner_scope(): void
    {
        $admin = new User(['role' => Role::ADMIN->value, 'status' => 'active']);
        $tenant = new TenantContext($admin);

        $partner = new Partner(['id' => 999, 'type' => 'sub']);
        $partner->id = 999;

        $this->assertTrue($this->policy->before($admin, 'view'));
        $this->assertTrue($tenant->canAccessPartner($partner));
    }
}
