<?php

namespace Tests\Feature\Customers;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Customers\Actions\ChangeCustomerPartnerMappingAction;
use App\Domain\Customers\Customer;
use App\Domain\Customers\CustomerPartnerAttribution;
use App\Domain\Partners\Partner;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use RuntimeException;
use Tests\TestCase;

class CustomerPartnerMappingTest extends TestCase
{
    public function test_admin_can_change_customer_partner_mapping_and_audits(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo('customer.mapping_changed'),
                $this->isInstanceOf(User::class),
                $this->isInstanceOf(Customer::class),
                $this->equalTo(['current_partner_id' => 1, 'current_sub_partner_id' => 10]),
                $this->equalTo(['current_partner_id' => 2, 'current_sub_partner_id' => 20])
            );

        $action = new class($logger) extends ChangeCustomerPartnerMappingAction
        {
            public bool $closedOldAttributions = false;

            public ?CustomerPartnerAttribution $createdAttribution = null;

            protected function lockCustomer(int|string $customerId): ?Customer
            {
                return new Customer(['id' => $customerId]);
            }

            protected function closeOpenAttributions(int|string $customerId, mixed $timestamp): int
            {
                $this->closedOldAttributions = true;

                return 1;
            }

            protected function createAttribution(array $attributes): CustomerPartnerAttribution
            {
                $this->createdAttribution = new CustomerPartnerAttribution($attributes);

                return $this->createdAttribution;
            }

            protected function updateCustomerMapping(Customer $customer, int|string $partnerId, int|string|null $subPartnerId): void
            {
                $customer->current_partner_id = $partnerId;
                $customer->current_sub_partner_id = $subPartnerId;
            }
        };

        $customer = new Customer([
            'customer_code' => 'CUST-001',
            'current_partner_id' => 1,
            'current_sub_partner_id' => 10,
        ]);
        $customer->id = 100;

        $newMainPartner = new Partner(['type' => 'main', 'status' => 'active']);
        $newMainPartner->id = 2;

        $newSubPartner = new Partner(['type' => 'sub', 'status' => 'active', 'parent_partner_id' => 2]);
        $newSubPartner->id = 20;

        $admin = new User(['role' => Role::ADMIN->value]);
        $admin->id = 99;

        $updated = $action->execute($customer, $newMainPartner, $newSubPartner, $admin);

        $this->assertEquals(2, $updated->current_partner_id);
        $this->assertEquals(20, $updated->current_sub_partner_id);
        $this->assertTrue($action->closedOldAttributions);
        $this->assertNotNull($action->createdAttribution);
        $this->assertEquals(2, $action->createdAttribution->partner_id);
        $this->assertEquals(20, $action->createdAttribution->sub_partner_id);
    }

    public function test_mapping_change_to_same_partner_configuration_throws_domain_exception(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $action = new ChangeCustomerPartnerMappingAction($logger);

        $customer = new Customer([
            'current_partner_id' => 1,
            'current_sub_partner_id' => 10,
        ]);
        $customer->id = 100;

        $mainPartner = new Partner(['type' => 'main', 'status' => 'active']);
        $mainPartner->id = 1;

        $subPartner = new Partner(['type' => 'sub', 'status' => 'active', 'parent_partner_id' => 1]);
        $subPartner->id = 10;

        $admin = new User(['role' => Role::ADMIN->value]);
        $admin->id = 99;

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Customer is already mapped to this partner configuration.');

        $action->execute($customer, $mainPartner, $subPartner, $admin);
    }

    public function test_sub_partner_not_belonging_to_main_partner_throws_domain_exception(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $action = new ChangeCustomerPartnerMappingAction($logger);

        $customer = new Customer([
            'current_partner_id' => 1,
            'current_sub_partner_id' => null,
        ]);
        $customer->id = 100;

        $mainPartner = new Partner(['type' => 'main', 'status' => 'active']);
        $mainPartner->id = 2;

        // Belongs to parent 999 instead of 2
        $subPartner = new Partner(['type' => 'sub', 'status' => 'active', 'parent_partner_id' => 999]);
        $subPartner->id = 20;

        $admin = new User(['role' => Role::ADMIN->value]);
        $admin->id = 99;

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Sub-partner does not belong to the selected main partner.');

        $action->execute($customer, $mainPartner, $subPartner, $admin);
    }

    public function test_mapping_unassigned_customer_throws_for_unconfirmed_behavior(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $action = new ChangeCustomerPartnerMappingAction($logger);

        // Unassigned customer (current_partner_id = null)
        $customer = new Customer([
            'current_partner_id' => null,
            'current_sub_partner_id' => null,
        ]);
        $customer->id = 100;

        $mainPartner = new Partner(['type' => 'main', 'status' => 'active']);
        $mainPartner->id = 2;

        $admin = new User(['role' => Role::ADMIN->value]);
        $admin->id = 99;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NEEDS BUSINESS CONFIRMATION #6');

        $action->execute($customer, $mainPartner, null, $admin);
    }

    public function test_mapping_to_inactive_partner_throws_for_unconfirmed_behavior(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $action = new ChangeCustomerPartnerMappingAction($logger);

        $customer = new Customer([
            'current_partner_id' => 1,
            'current_sub_partner_id' => null,
        ]);
        $customer->id = 100;

        $inactiveMainPartner = new Partner(['type' => 'main', 'status' => 'inactive']);
        $inactiveMainPartner->id = 2;

        $admin = new User(['role' => Role::ADMIN->value]);
        $admin->id = 99;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NEEDS BUSINESS CONFIRMATION #7');

        $action->execute($customer, $inactiveMainPartner, null, $admin);
    }

    public function test_non_admin_cannot_change_customer_mapping(): void
    {
        $mainUser = new User(['role' => Role::MAIN_PARTNER->value]);
        $subUser = new User(['role' => Role::SUB_PARTNER->value]);
        $customer = new Customer(['current_partner_id' => 1]);

        $this->assertFalse(Gate::forUser($mainUser)->allows('changeMapping', $customer));
        $this->assertFalse(Gate::forUser($subUser)->allows('changeMapping', $customer));
    }
}
