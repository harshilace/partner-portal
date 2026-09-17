<?php

namespace Tests\Feature\Customers;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Partners\Partner;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CustomerPolicyTest extends TestCase
{
    public function test_admin_has_unrestricted_customer_access_via_before(): void
    {
        $admin = new User(['role' => Role::ADMIN->value]);
        $customer = new Customer(['current_partner_id' => 999]);

        $this->assertTrue(Gate::forUser($admin)->allows('view', $customer));
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Customer::class));
        $this->assertTrue(Gate::forUser($admin)->allows('changeMapping', $customer));
    }

    public function test_sub_partner_can_view_own_customer_only(): void
    {
        $subPartnerA = new Partner(['type' => 'sub']);
        $subPartnerA->id = 5;

        $userA = new class(['role' => Role::SUB_PARTNER->value]) extends User
        {
            public ?Partner $mockPartner = null;

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $userA->mockPartner = $subPartnerA;

        $ownCustomer = new Customer(['current_partner_id' => 1, 'current_sub_partner_id' => 5]);
        $otherSubCustomer = new Customer(['current_partner_id' => 1, 'current_sub_partner_id' => 6]);

        $this->assertTrue(Gate::forUser($userA)->allows('view', $ownCustomer));
        $this->assertFalse(Gate::forUser($userA)->allows('view', $otherSubCustomer));
    }

    public function test_main_partner_can_view_own_direct_customer(): void
    {
        $mainPartner = new Partner(['type' => 'main']);
        $mainPartner->id = 10;

        $user = new class(['role' => Role::MAIN_PARTNER->value]) extends User
        {
            public ?Partner $mockPartner = null;

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $user->mockPartner = $mainPartner;

        $ownDirectCustomer = new Customer(['current_partner_id' => 10, 'current_sub_partner_id' => null]);
        $otherMainCustomer = new Customer(['current_partner_id' => 20, 'current_sub_partner_id' => null]);

        $this->assertTrue(Gate::forUser($user)->allows('view', $ownDirectCustomer));
        $this->assertFalse(Gate::forUser($user)->allows('view', $otherMainCustomer));
    }

    public function test_main_partner_cannot_view_sub_partner_customer_pending_confirmation(): void
    {
        $mainPartner = new Partner(['type' => 'main']);
        $mainPartner->id = 10;

        $user = new class(['role' => Role::MAIN_PARTNER->value]) extends User
        {
            public ?Partner $mockPartner = null;

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $user->mockPartner = $mainPartner;

        // Customer attributed to sub-partner 50
        $subPartnerCustomer = new Customer(['current_partner_id' => 10, 'current_sub_partner_id' => 50]);

        $this->assertFalse(Gate::forUser($user)->allows('view', $subPartnerCustomer));
    }

    public function test_historical_partner_cannot_view_customer_profile_after_remapping(): void
    {
        $historicalPartner = new Partner(['type' => 'main']);
        $historicalPartner->id = 10;

        $user = new class(['role' => Role::MAIN_PARTNER->value]) extends User
        {
            public ?Partner $mockPartner = null;

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $user->mockPartner = $historicalPartner;

        // Customer currently mapped to Partner 20
        $remappedCustomer = new Customer(['current_partner_id' => 20, 'current_sub_partner_id' => null]);

        $this->assertFalse(Gate::forUser($user)->allows('view', $remappedCustomer));
    }

    public function test_non_admin_cannot_delete_customer(): void
    {
        $mainUser = new User(['role' => Role::MAIN_PARTNER->value]);
        $subUser = new User(['role' => Role::SUB_PARTNER->value]);
        $admin = new User(['role' => Role::ADMIN->value]);
        $customer = new Customer(['current_partner_id' => 1]);

        $this->assertFalse(Gate::forUser($mainUser)->allows('delete', $customer));
        $this->assertFalse(Gate::forUser($subUser)->allows('delete', $customer));
        // Even Admin delete returns false (ON DELETE RESTRICT preservation)
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $customer)); // handled by before()
    }
}
