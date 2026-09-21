<?php

namespace Tests\Feature\Hardening;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Customers\Actions\ChangeCustomerPartnerMappingAction;
use App\Domain\Customers\Customer;
use App\Domain\Customers\CustomerPartnerAttribution;
use App\Domain\Partners\Partner;
use App\Domain\Payments\Actions\ProcessSaleAction;
use App\Domain\Payments\Order;
use App\Domain\Payments\OrderItem;
use App\Domain\Payments\Payment;
use App\Domain\Payments\PaymentTransaction;
use App\Domain\Payments\Services\NullPaymentGateway;
use App\Domain\Products\Product;
use App\Domain\Products\ProductPlan;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionStatusHistory;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HistoricalImmutabilityHardeningTest extends TestCase
{
    /**
     * Historical order and subscription ownership snapshots remain completely unchanged
     * when an Admin updates a customer's partner mapping.
     */
    public function test_historical_ownership_snapshots_are_immutable_under_customer_partner_remapping(): void
    {
        $gateway = new NullPaymentGateway;
        $logger = $this->createMock(AuditLogger::class);

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $partnerA = new Partner(['id' => 10, 'type' => 'main', 'status' => 'active']);
        $partnerA->id = 10;

        $subPartnerA = new Partner(['id' => 101, 'type' => 'sub', 'parent_partner_id' => 10, 'status' => 'active']);
        $subPartnerA->id = 101;

        $partnerB = new Partner(['id' => 20, 'type' => 'main', 'status' => 'active']);
        $partnerB->id = 20;

        $subPartnerB = new Partner(['id' => 201, 'type' => 'sub', 'parent_partner_id' => 20, 'status' => 'active']);
        $subPartnerB->id = 201;

        // Customer initially mapped to Partner A and Sub-Partner A
        $customer = new Customer([
            'id' => 500,
            'current_partner_id' => 10,
            'current_sub_partner_id' => 101,
        ]);
        $customer->id = 500;

        $product = new Product(['id' => 1, 'is_active' => true]);
        $plan = new ProductPlan(['id' => 1, 'product_id' => 1, 'price' => '50.00', 'is_active' => true]);
        $plan->setRelation('product', $product);

        $saleAction = new class($gateway, $logger) extends ProcessSaleAction
        {
            protected function runInTransaction(callable $callback): mixed
            {
                return $callback();
            }

            protected function createOrder(array $attributes): Order
            {
                return new Order($attributes);
            }

            protected function createOrderItem(array $attributes): OrderItem
            {
                return new OrderItem($attributes);
            }

            protected function createPayment(array $attributes): Payment
            {
                return new Payment($attributes);
            }

            protected function createPaymentTransaction(array $attributes): PaymentTransaction
            {
                return new PaymentTransaction($attributes);
            }

            protected function createSubscription(array $attributes): Subscription
            {
                return new Subscription($attributes);
            }

            protected function createSubscriptionStatusHistory(array $attributes): SubscriptionStatusHistory
            {
                return new SubscriptionStatusHistory($attributes);
            }
        };

        // 1. Generate historical order and subscription under Partner A
        $historicalSale = $saleAction->execute(['customer' => $customer, 'plan' => $plan], $admin);
        $historicalOrder = $historicalSale['order'];
        $historicalSubscription = $historicalSale['subscription'];

        $this->assertEquals(10, $historicalOrder->partner_id);
        $this->assertEquals(101, $historicalOrder->sub_partner_id);
        $this->assertEquals(10, $historicalSubscription->partner_id);
        $this->assertEquals(101, $historicalSubscription->sub_partner_id);

        // 2. Admin executes customer partner mapping change to Partner B
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $mappingAction = new class($logger) extends ChangeCustomerPartnerMappingAction
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

        $mappingAction->execute($customer, $partnerB, $subPartnerB, $admin);

        // Verify customer's current mapping is updated
        $this->assertEquals(20, $customer->current_partner_id);
        $this->assertEquals(201, $customer->current_sub_partner_id);

        // 3. Verify historical order and subscription retain Partner A snapshots permanently
        $this->assertEquals(
            10,
            $historicalOrder->partner_id,
            'Historical order partner_id snapshot must not change upon customer remapping.'
        );
        $this->assertEquals(
            101,
            $historicalOrder->sub_partner_id,
            'Historical order sub_partner_id snapshot must not change upon customer remapping.'
        );
        $this->assertEquals(
            10,
            $historicalSubscription->partner_id,
            'Historical subscription partner_id snapshot must not change upon customer remapping.'
        );
        $this->assertEquals(
            101,
            $historicalSubscription->sub_partner_id,
            'Historical subscription sub_partner_id snapshot must not change upon customer remapping.'
        );

        // 4. Verify subsequent sale correctly snapshots new Partner B ownership
        $newSale = $saleAction->execute(['customer' => $customer, 'plan' => $plan], $admin);
        $this->assertEquals(20, $newSale['order']->partner_id);
        $this->assertEquals(201, $newSale['order']->sub_partner_id);
        $this->assertEquals(20, $newSale['subscription']->partner_id);
        $this->assertEquals(201, $newSale['subscription']->sub_partner_id);
    }
}
