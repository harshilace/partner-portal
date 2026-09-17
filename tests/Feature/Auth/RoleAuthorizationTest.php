<?php

namespace Tests\Feature\Auth;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use App\Domain\Subscriptions\Subscription;
use App\Policies\PartnerPolicy;
use App\Policies\SubscriptionPolicy;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    protected PartnerPolicy $partnerPolicy;

    protected SubscriptionPolicy $subscriptionPolicy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->partnerPolicy = new PartnerPolicy;
        $this->subscriptionPolicy = new SubscriptionPolicy;
    }

    public function test_user_role_and_status_helper_methods(): void
    {
        $admin = new User(['role' => Role::ADMIN->value, 'status' => 'active']);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isMainPartner());
        $this->assertFalse($admin->isSubPartner());
        $this->assertTrue($admin->isActive());

        $mainPartnerUser = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $this->assertFalse($mainPartnerUser->isAdmin());
        $this->assertTrue($mainPartnerUser->isMainPartner());
        $this->assertFalse($mainPartnerUser->isSubPartner());

        $subPartnerUser = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'inactive']);
        $this->assertFalse($subPartnerUser->isAdmin());
        $this->assertFalse($subPartnerUser->isMainPartner());
        $this->assertTrue($subPartnerUser->isSubPartner());
        $this->assertFalse($subPartnerUser->isActive());
    }

    public function test_admin_has_unrestricted_partner_management_authority(): void
    {
        $admin = new User(['role' => Role::ADMIN->value, 'status' => 'active']);
        $partner = new Partner(['id' => 10, 'type' => 'main']);

        $this->assertTrue($this->partnerPolicy->before($admin, 'view'));
        $this->assertTrue($this->partnerPolicy->before($admin, 'createSubPartner'));
        $this->assertTrue($this->partnerPolicy->before($admin, 'update'));
        $this->assertTrue($this->partnerPolicy->before($admin, 'delete'));
    }

    public function test_main_partner_can_create_sub_partner_under_own_partner(): void
    {
        $mainPartner = new Partner(['id' => 100, 'type' => 'main']);
        $mainPartner->id = 100;

        $user = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $user->setRelation('partners', collect([$mainPartner]));

        $this->assertTrue($this->partnerPolicy->createSubPartner($user, $mainPartner));
    }

    public function test_sub_partner_is_strictly_denied_from_creating_sub_partners(): void
    {
        $subPartner = new Partner(['id' => 200, 'type' => 'sub', 'parent_partner_id' => 100]);
        $subPartner->id = 200;

        $user = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $user->setRelation('partners', collect([$subPartner]));

        // Sub-Partner must be denied (Section 4, 5)
        $this->assertFalse($this->partnerPolicy->createSubPartner($user, $subPartner));
        $this->assertFalse($this->partnerPolicy->createSubPartner($user, null));
    }

    public function test_sub_partner_cannot_view_another_sub_partner_record(): void
    {
        $subPartner1 = new Partner(['id' => 201, 'type' => 'sub', 'parent_partner_id' => 100]);
        $subPartner1->id = 201;

        $subPartner2 = new Partner(['id' => 202, 'type' => 'sub', 'parent_partner_id' => 100]);
        $subPartner2->id = 202;

        $user = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $user->setRelation('partners', collect([$subPartner1]));

        $this->assertTrue($this->partnerPolicy->view($user, $subPartner1));
        $this->assertFalse($this->partnerPolicy->view($user, $subPartner2));
    }

    public function test_admin_has_exclusive_authority_to_cancel_subscription(): void
    {
        $admin = new User(['role' => Role::ADMIN->value, 'status' => 'active']);
        $subscription = new Subscription(['id' => 50, 'partner_id' => 100]);

        $this->assertTrue($this->subscriptionPolicy->before($admin, 'cancel'));

        $mainUser = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $this->assertNull($this->subscriptionPolicy->before($mainUser, 'cancel'));
        $this->assertFalse($this->subscriptionPolicy->cancel($mainUser, $subscription));

        $subUser = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $this->assertNull($this->subscriptionPolicy->before($subUser, 'cancel'));
        $this->assertFalse($this->subscriptionPolicy->cancel($subUser, $subscription));
    }

    public function test_admin_has_exclusive_authority_to_stop_auto_debit(): void
    {
        $admin = new User(['role' => Role::ADMIN->value, 'status' => 'active']);
        $subscription = new Subscription(['id' => 50, 'partner_id' => 100]);

        $this->assertTrue($this->subscriptionPolicy->before($admin, 'stopAutoDebit'));

        $mainUser = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $this->assertNull($this->subscriptionPolicy->before($mainUser, 'stopAutoDebit'));
        $this->assertFalse($this->subscriptionPolicy->stopAutoDebit($mainUser, $subscription));

        $subUser = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $this->assertNull($this->subscriptionPolicy->before($subUser, 'stopAutoDebit'));
        $this->assertFalse($this->subscriptionPolicy->stopAutoDebit($subUser, $subscription));
    }

    public function test_subscription_view_policy_scopes_to_partner_ownership(): void
    {
        $subPartner = new Partner(['id' => 301, 'type' => 'sub']);
        $subPartner->id = 301;

        $user = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $user->setRelation('partners', collect([$subPartner]));

        $ownSubscription = new Subscription(['id' => 1, 'sub_partner_id' => 301]);
        $otherSubscription = new Subscription(['id' => 2, 'sub_partner_id' => 302]);

        $this->assertTrue($this->subscriptionPolicy->view($user, $ownSubscription));
        $this->assertFalse($this->subscriptionPolicy->view($user, $otherSubscription));
    }
}
