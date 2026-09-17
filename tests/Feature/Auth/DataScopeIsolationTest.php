<?php

namespace Tests\Feature\Auth;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\Services\TenantContext;
use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Leads\Lead;
use App\Domain\Partners\Partner;
use App\Domain\Payments\Order;
use App\Domain\Subscriptions\Subscription;
use Tests\TestCase;

class DataScopeIsolationTest extends TestCase
{
    public function test_admin_has_unrestricted_tenant_access(): void
    {
        $admin = new User(['role' => Role::ADMIN->value, 'status' => 'active']);
        $context = new TenantContext($admin);

        $this->assertTrue($context->canAccessPartner(999));
        $this->assertTrue($context->canAccessRecord(new Customer(['current_partner_id' => 888])));
    }

    public function test_main_partner_access_scopes_to_self_and_subordinates(): void
    {
        $mainPartner = new Partner(['id' => 10, 'type' => 'main']);
        $mainPartner->id = 10;

        $subPartner = new Partner(['id' => 20, 'parent_partner_id' => 10, 'type' => 'sub']);
        $subPartner->id = 20;

        $foreignMain = new Partner(['id' => 99, 'type' => 'main']);
        $foreignMain->id = 99;

        $user = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $user->setRelation('partners', collect([$mainPartner]));

        $context = new TenantContext($user);

        $this->assertTrue($context->canAccessPartner($mainPartner));
        $this->assertTrue($context->canAccessPartner($subPartner));
        $this->assertFalse($context->canAccessPartner($foreignMain));
    }

    public function test_sub_partner_access_strictly_prevents_idor_to_other_sub_partners(): void
    {
        $subPartner1 = new Partner(['id' => 201, 'parent_partner_id' => 10, 'type' => 'sub']);
        $subPartner1->id = 201;

        $subPartner2 = new Partner(['id' => 202, 'parent_partner_id' => 10, 'type' => 'sub']);
        $subPartner2->id = 202;

        $user = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $user->setRelation('partners', collect([$subPartner1]));

        $context = new TenantContext($user);

        // Can access own partner
        $this->assertTrue($context->canAccessPartner(201));
        $this->assertTrue($context->canAccessPartner($subPartner1));

        // CANNOT access another sub-partner (IDOR protection)
        $this->assertFalse($context->canAccessPartner(202));
        $this->assertFalse($context->canAccessPartner($subPartner2));
    }

    public function test_sub_partner_record_access_enforces_tenant_boundary(): void
    {
        $subPartner = new Partner(['id' => 300, 'type' => 'sub']);
        $subPartner->id = 300;

        $user = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $user->setRelation('partners', collect([$subPartner]));

        $context = new TenantContext($user);

        $ownLead = new Lead(['id' => 1, 'sub_partner_id' => 300]);
        $otherLead = new Lead(['id' => 2, 'sub_partner_id' => 301]);

        $this->assertTrue($context->canAccessRecord($ownLead));
        $this->assertFalse($context->canAccessRecord($otherLead));

        $ownOrder = new Order(['id' => 10, 'sub_partner_id' => 300]);
        $otherOrder = new Order(['id' => 11, 'sub_partner_id' => 301]);

        $this->assertTrue($context->canAccessRecord($ownOrder));
        $this->assertFalse($context->canAccessRecord($otherOrder));
    }

    public function test_query_scoping_applies_sub_partner_boundary(): void
    {
        $subPartner = new Partner(['id' => 400, 'type' => 'sub']);
        $subPartner->id = 400;

        $user = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $user->setRelation('partners', collect([$subPartner]));

        $context = new TenantContext($user);

        $query = (new Subscription)->newQuery();
        $scoped = $context->scopeQuery($query);

        $this->assertStringContainsString('sub_partner_id', $scoped->toSql());
        $this->assertEquals([400], $scoped->getBindings());
    }

    public function test_query_scoping_applies_main_partner_boundary(): void
    {
        $mainPartner = new Partner(['id' => 500, 'type' => 'main']);
        $mainPartner->id = 500;

        $user = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $user->setRelation('partners', collect([$mainPartner]));

        $context = new TenantContext($user);

        $query = (new Subscription)->newQuery();
        $scoped = $context->scopeQuery($query);

        $this->assertStringContainsString('partner_id', $scoped->toSql());
        $this->assertEquals([500], $scoped->getBindings());
    }
}
