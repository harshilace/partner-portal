<?php

namespace Tests\Feature\Customers;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Customers\Actions\ChangeCustomerPartnerMappingAction;
use App\Domain\Customers\Customer;
use App\Domain\Customers\CustomerPartnerAttribution;
use App\Domain\Partners\Partner;
use App\Domain\Payments\Order;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerHistoricalPreservationTest extends TestCase
{
    public function test_mapping_change_preserves_historical_order_and_subscription_ownership(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);

        $action = new class($logger) extends ChangeCustomerPartnerMappingAction
        {
            protected function lockCustomer(int|string $customerId): ?Customer
            {
                return new Customer(['id' => $customerId]);
            }

            protected function closeOpenAttributions(int|string $customerId, mixed $timestamp): int
            {
                return 1;
            }

            protected function createAttribution(array $attributes): CustomerPartnerAttribution
            {
                return new CustomerPartnerAttribution($attributes);
            }

            protected function updateCustomerMapping(Customer $customer, int|string $partnerId, int|string|null $subPartnerId): void
            {
                $customer->current_partner_id = $partnerId;
                $customer->current_sub_partner_id = $subPartnerId;
            }
        };

        $customer = new Customer([
            'id' => 50,
            'current_partner_id' => 1,
            'current_sub_partner_id' => null,
        ]);
        $customer->id = 50;

        // Existing order created before mapping change: snapshot belongs to Partner 1
        $historicalOrder = new Order([
            'customer_id' => 50,
            'partner_id' => 1,
            'sub_partner_id' => null,
            'order_number' => 'ORD-1001',
        ]);
        $historicalOrder->id = 101;

        // Existing subscription created before mapping change: snapshot belongs to Partner 1
        $historicalSubscription = new Subscription([
            'customer_id' => 50,
            'partner_id' => 1,
            'sub_partner_id' => null,
            'subscription_number' => 'SUB-2001',
        ]);
        $historicalSubscription->id = 201;

        $newPartner = new Partner(['type' => 'main', 'status' => 'active']);
        $newPartner->id = 2;

        $admin = new User(['role' => Role::ADMIN->value]);
        $admin->id = 1;

        $action->execute($customer, $newPartner, null, $admin);

        // Customer mapping updated to Partner 2
        $this->assertEquals(2, $customer->current_partner_id);

        // Historical order and subscription ownership MUST REMAIN untouched (Partner 1)
        $this->assertEquals(1, $historicalOrder->partner_id);
        $this->assertNull($historicalOrder->sub_partner_id);
        $this->assertEquals(1, $historicalSubscription->partner_id);
        $this->assertNull($historicalSubscription->sub_partner_id);
    }
}
