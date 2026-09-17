<?php

namespace Tests\Feature\Customers;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CustomerManualCreatePolicyTest extends TestCase
{
    public function test_non_admin_roles_cannot_manually_create_customers(): void
    {
        $mainUser = new User(['role' => Role::MAIN_PARTNER->value]);
        $subUser = new User(['role' => Role::SUB_PARTNER->value]);
        $partnerUser = new User(['role' => Role::PARTNER_USER->value]);

        $this->assertFalse(Gate::forUser($mainUser)->allows('create', Customer::class));
        $this->assertFalse(Gate::forUser($subUser)->allows('create', Customer::class));
        $this->assertFalse(Gate::forUser($partnerUser)->allows('create', Customer::class));
    }

    public function test_admin_is_authorized_to_create_customer_via_before(): void
    {
        $admin = new User(['role' => Role::ADMIN->value]);

        $this->assertTrue(Gate::forUser($admin)->allows('create', Customer::class));
    }

    public function test_non_admin_roles_cannot_update_customer_profiles(): void
    {
        $mainUser = new User(['role' => Role::MAIN_PARTNER->value]);
        $subUser = new User(['role' => Role::SUB_PARTNER->value]);
        $customer = new Customer(['current_partner_id' => 1]);

        $this->assertFalse(Gate::forUser($mainUser)->allows('update', $customer));
        $this->assertFalse(Gate::forUser($subUser)->allows('update', $customer));
    }
}
