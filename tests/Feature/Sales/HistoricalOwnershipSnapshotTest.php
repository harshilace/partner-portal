<?php

namespace Tests\Feature\Sales;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
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
use Tests\TestCase;

class HistoricalOwnershipSnapshotTest extends TestCase
{
    public function test_orders_and_subscriptions_retain_historical_snapshots_after_mapping_change(): void
    {
        $gateway = new NullPaymentGateway;
        $logger = $this->createMock(AuditLogger::class);

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $product = new Product(['id' => 10, 'is_active' => true]);
        $plan = new ProductPlan(['id' => 100, 'product_id' => 10, 'price' => '100.00', 'is_active' => true]);
        $plan->setRelation('product', $product);

        $action = new class($gateway, $logger) extends ProcessSaleAction
        {
            protected function runInTransaction(callable $callback): mixed
            {
                return $callback();
            }

            protected function createOrder(array $attributes): Order
            {
                $order = new Order($attributes);
                $order->id = rand(100, 999);

                return $order;
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
                $sub = new Subscription($attributes);
                $sub->id = rand(100, 999);

                return $sub;
            }

            protected function createSubscriptionStatusHistory(array $attributes): SubscriptionStatusHistory
            {
                return new SubscriptionStatusHistory($attributes);
            }
        };

        // 1. Initial sale when customer is mapped to Partner A (id 2)
        $customer = new Customer([
            'id' => 50,
            'current_partner_id' => 2,
            'current_sub_partner_id' => 20,
        ]);

        $sale1 = $action->execute(['customer' => $customer, 'plan' => $plan], $admin);

        $this->assertEquals(2, $sale1['order']->partner_id);
        $this->assertEquals(20, $sale1['order']->sub_partner_id);
        $this->assertEquals(2, $sale1['subscription']->partner_id);
        $this->assertEquals(20, $sale1['subscription']->sub_partner_id);

        // 2. Customer partner mapping is updated to Partner B (id 3)
        $customer->current_partner_id = 3;
        $customer->current_sub_partner_id = 30;

        // 3. Historical records must still retain Partner A snapshot
        $this->assertEquals(2, $sale1['order']->partner_id);
        $this->assertEquals(20, $sale1['order']->sub_partner_id);
        $this->assertEquals(2, $sale1['subscription']->partner_id);
        $this->assertEquals(20, $sale1['subscription']->sub_partner_id);

        // 4. Future sale uses new Partner B mapping
        $sale2 = $action->execute(['customer' => $customer, 'plan' => $plan], $admin);

        $this->assertEquals(3, $sale2['order']->partner_id);
        $this->assertEquals(30, $sale2['order']->sub_partner_id);
        $this->assertEquals(3, $sale2['subscription']->partner_id);
        $this->assertEquals(30, $sale2['subscription']->sub_partner_id);
    }
}
