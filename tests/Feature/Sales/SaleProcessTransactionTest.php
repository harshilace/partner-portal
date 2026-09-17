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

class SaleProcessTransactionTest extends TestCase
{
    public function test_sale_executes_transactionally_and_creates_all_records(): void
    {
        $gateway = new NullPaymentGateway;
        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())->method('log');

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $customer = new Customer([
            'current_partner_id' => 2,
            'current_sub_partner_id' => 20,
        ]);
        $customer->id = 50;

        $product = new Product(['is_active' => true]);
        $product->id = 10;
        $plan = new ProductPlan(['product_id' => 10, 'price' => '99.00', 'is_active' => true]);
        $plan->id = 100;
        $plan->setRelation('product', $product);

        $action = new class($gateway, $logger) extends ProcessSaleAction
        {
            public array $createdRecords = [];

            protected function runInTransaction(callable $callback): mixed
            {
                return $callback();
            }

            protected function createOrder(array $attributes): Order
            {
                $order = new Order($attributes);
                $order->id = 500;
                $this->createdRecords['order'] = $order;

                return $order;
            }

            protected function createOrderItem(array $attributes): OrderItem
            {
                $item = new OrderItem($attributes);
                $item->id = 501;
                $this->createdRecords['order_item'] = $item;

                return $item;
            }

            protected function createPayment(array $attributes): Payment
            {
                $payment = new Payment($attributes);
                $payment->id = 600;
                $this->createdRecords['payment'] = $payment;

                return $payment;
            }

            protected function createPaymentTransaction(array $attributes): PaymentTransaction
            {
                $txn = new PaymentTransaction($attributes);
                $txn->id = 700;
                $this->createdRecords['transaction'] = $txn;

                return $txn;
            }

            protected function createSubscription(array $attributes): Subscription
            {
                $sub = new Subscription($attributes);
                $sub->id = 800;
                $this->createdRecords['subscription'] = $sub;

                return $sub;
            }

            protected function createSubscriptionStatusHistory(array $attributes): SubscriptionStatusHistory
            {
                $history = new SubscriptionStatusHistory($attributes);
                $history->id = 900;
                $this->createdRecords['history'] = $history;

                return $history;
            }
        };

        $result = $action->execute([
            'customer' => $customer,
            'plan' => $plan,
            'quantity' => 2,
            'payment_method' => 'card',
        ], $admin);

        $this->assertNotNull($result['order']);
        $this->assertNotNull($result['payment']);
        $this->assertNotNull($result['subscription']);

        /** @var Order $order */
        $order = $action->createdRecords['order'];
        $this->assertEquals(50, $order->customer_id);
        $this->assertEquals(2, $order->partner_id);
        $this->assertEquals(20, $order->sub_partner_id);
        $this->assertEquals(198.00, (float) $order->total_amount);

        /** @var OrderItem $item */
        $item = $action->createdRecords['order_item'];
        $this->assertEquals(500, $item->order_id);
        $this->assertEquals(10, $item->product_id);
        $this->assertEquals(100, $item->product_plan_id);
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals(99.00, (float) $item->unit_price);
        $this->assertEquals(198.00, (float) $item->total_price);

        /** @var Subscription $subscription */
        $subscription = $action->createdRecords['subscription'];
        $this->assertEquals(50, $subscription->customer_id);
        $this->assertEquals(500, $subscription->order_id);
        $this->assertEquals(2, $subscription->partner_id);
        $this->assertEquals(20, $subscription->sub_partner_id);

        /** @var SubscriptionStatusHistory $history */
        $history = $action->createdRecords['history'];
        $this->assertEquals(800, $history->subscription_id);
        $this->assertEquals('active', $history->to_status);
        $this->assertEquals('Initial purchase', $history->reason);
    }
}
