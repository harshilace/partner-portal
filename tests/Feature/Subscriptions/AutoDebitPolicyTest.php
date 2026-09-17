<?php

namespace Tests\Feature\Subscriptions;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use App\Domain\Subscriptions\AutoDebitMandate;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AutoDebitPolicyTest extends TestCase
{
    public function test_admin_can_stop_mandate_but_cannot_create_unconfirmed_mandate(): void
    {
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);
        $mandate = new AutoDebitMandate(['id' => 10, 'status' => 'active']);

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', AutoDebitMandate::class));
        $this->assertTrue(Gate::forUser($admin)->allows('view', $mandate));
        $this->assertTrue(Gate::forUser($admin)->allows('stop', $mandate));

        // Mandate creation is strictly blocked pending business confirmation
        $this->assertFalse(Gate::forUser($admin)->allows('create', AutoDebitMandate::class));
    }

    public function test_partners_cannot_stop_or_create_auto_debit_mandates(): void
    {
        $mainPartnerUser = new User(['id' => 2, 'role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $subPartnerUser = new User(['id' => 3, 'role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $mandate = new AutoDebitMandate(['id' => 10, 'status' => 'active']);

        // Main Partner cannot stop or create
        $this->assertFalse(Gate::forUser($mainPartnerUser)->allows('stop', $mandate));
        $this->assertFalse(Gate::forUser($mainPartnerUser)->allows('create', AutoDebitMandate::class));

        // Sub-Partner cannot stop or create
        $this->assertFalse(Gate::forUser($subPartnerUser)->allows('stop', $mandate));
        $this->assertFalse(Gate::forUser($subPartnerUser)->allows('create', AutoDebitMandate::class));
    }

    public function test_sub_partner_can_only_view_own_subscription_mandates(): void
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
        $ownMandate = new AutoDebitMandate(['id' => 101]);
        $ownMandate->setRelation('subscription', $ownSub);

        $otherSub = new Subscription(['partner_id' => 2, 'sub_partner_id' => 21]);
        $otherMandate = new AutoDebitMandate(['id' => 102]);
        $otherMandate->setRelation('subscription', $otherSub);

        $this->assertTrue(Gate::forUser($subUser)->allows('view', $ownMandate));
        $this->assertFalse(Gate::forUser($subUser)->allows('view', $otherMandate));
    }

    public function test_main_partner_access_to_sub_partner_mandate_rows_is_blocked(): void
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

        // Direct mandate under Main Partner
        $directSub = new Subscription(['partner_id' => 2, 'sub_partner_id' => null]);
        $directMandate = new AutoDebitMandate(['id' => 201]);
        $directMandate->setRelation('subscription', $directSub);

        // Mandate under Sub-Partner
        $subPartnerSub = new Subscription(['partner_id' => 2, 'sub_partner_id' => 20]);
        $subPartnerMandate = new AutoDebitMandate(['id' => 202]);
        $subPartnerMandate->setRelation('subscription', $subPartnerSub);

        $this->assertTrue(Gate::forUser($mainUser)->allows('view', $directMandate));
        // Sub-Partner row access is blocked pending confirmation
        $this->assertFalse(Gate::forUser($mainUser)->allows('view', $subPartnerMandate));
    }
}
