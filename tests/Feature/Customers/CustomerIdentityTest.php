<?php

namespace Tests\Feature\Customers;

use App\Domain\Customers\Customer;
use Tests\TestCase;

class CustomerIdentityTest extends TestCase
{
    public function test_email_normalization_trims_and_lowercases(): void
    {
        $this->assertEquals('user@example.com', Customer::normalizeEmail('  User@Example.COM  '));
        $this->assertEquals('test.account+sub@domain.co.in', Customer::normalizeEmail(' Test.Account+Sub@Domain.CO.IN '));
        $this->assertNull(Customer::normalizeEmail(null));
    }

    public function test_mobile_normalization_strips_non_digits(): void
    {
        $this->assertEquals('919876543210', Customer::normalizeMobile('+91 98765-43210'));
        $this->assertEquals('1234567890', Customer::normalizeMobile('(123) 456-7890'));
        $this->assertEquals('19998887776', Customer::normalizeMobile('  +1-999-888-7776  '));
        $this->assertNull(Customer::normalizeMobile(null));
    }

    public function test_customer_is_assigned_helper(): void
    {
        $assigned = new Customer(['current_partner_id' => 1]);
        $unassigned = new Customer(['current_partner_id' => null]);

        $this->assertTrue($assigned->isAssigned());
        $this->assertFalse($unassigned->isAssigned());
    }
}
