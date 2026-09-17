<?php

namespace Tests\Feature\Subscriptions;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SubscriptionPolicyTest extends TestCase
{
    public function test_admin_can_view_and_cancel_any_subscription(): void
    {
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);
        $sub = new Subscription(['id' => 10, 'partner_id' => 2, 'sub_partner_id' => 20]);

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Subscription::class));
        $this->assertTrue(Gate::forUser($admin)->allows('view', $sub));
        $this->assertTrue(Gate::forUser($admin)->allows('cancel', $sub));
    }

    public function test_partners_cannot_cancel_subscriptions(): void
    {
        $partnerUser = new User(['id' => 2, 'role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $sub = new Subscription(['id' => 10, 'partner_id' => 2, 'sub_partner_id' => 20]);

        $this->assertFalse(Gate::forUser($partnerUser)->allows('cancel', $sub));
        $this->assertFalse(Gate::forUser($partnerUser)->allows('stopAutoDebit', $sub));
    }

    public function test_sub_partner_can_only_view_own_subscriptions(): void
    {
        $subPartner = new Partner(['type' => 'sub', 'status' => 'active']);
        $subPartner->id = 20;
        $subUser = new class(['id' => 3, 'role' => Role::SUB_PARTNER->value, 'status' => 'active']) extends User
        {
            public ?Partner $mockPartner = null;

            public function isSubPartner(): bool
            {
                return true;
            }

            public function isMainPartner(): bool
            {
                return false;
            }

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $subUser->mockPartner = $subPartner;

        $ownSub = new Subscription(['partner_id' => 2, 'sub_partner_id' => 20]);
        $otherSub = new Subscription(['partner_id' => 2, 'sub_partner_id' => 21]);

        $this->assertTrue(Gate::forUser($subUser)->allows('view', $ownSub));
        $this->assertFalse(Gate::forUser($subUser)->allows('view', $otherSub));
    }

    public function test_main_partner_sub_partner_subscription_rows_are_blocked(): void
    {
        $mainPartner = new Partner(['type' => 'main', 'status' => 'active']);
        $mainPartner->id = 2;
        $mainUser = new class(['id' => 2, 'role' => Role::MAIN_PARTNER->value, 'status' => 'active']) extends User
        {
            public ?Partner $mockPartner = null;

            public function isSubPartner(): bool
            {
                return false;
            }

            public function isMainPartner(): bool
            {
                return true;
            }

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $mainUser->mockPartner = $mainPartner;

        $directSub = new Subscription(['partner_id' => 2, 'sub_partner_id' => null]);
        $subPartnerSub = new Subscription(['partner_id' => 2, 'sub_partner_id' => 20]);

        $this->assertTrue(Gate::forUser($mainUser)->allows('view', $directSub));
        // Sub-partner row access is blocked pending confirmation
        $this->assertFalse(Gate::forUser($mainUser)->allows('view', $subPartnerSub));
    }
}
