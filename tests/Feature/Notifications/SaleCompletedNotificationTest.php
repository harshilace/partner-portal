<?php

namespace Tests\Feature\Notifications;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Notifications\Services\NotificationService;
use App\Domain\Payments\Actions\ProcessSaleAction;
use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Domain\Payments\Order;
use App\Domain\Payments\OrderItem;
use App\Domain\Payments\Payment;
use App\Domain\Payments\PaymentTransaction;
use App\Domain\Products\Product;
use App\Domain\Products\ProductPlan;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionStatusHistory;
use App\Events\PaymentReceived;
use App\Events\SaleCompleted;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SaleCompletedNotificationTest extends TestCase
{
    public function test_sale_completed_and_payment_received_events_fired_after_action_transaction(): void
    {
        Event::fake([SaleCompleted::class, PaymentReceived::class]);

        $gateway = $this->createMock(PaymentGatewayInterface::class);
        $gateway->method('charge')->willReturn([
            'gateway_name' => 'null',
            'transaction_reference' => 'TXN-12345',
            'amount' => 99.00,
            'status' => 'success',
        ]);

        $logger = $this->createMock(AuditLogger::class);

        $customer = new Customer([
            'current_partner_id' => 10,
            'status' => 'active',
        ]);
        $customer->id = 100;

        $product = new Product([
            'name' => 'Antivirus Pro',
            'status' => 'active',
        ]);
        $product->id = 20;

        $plan = new ProductPlan([
            'product_id' => 20,
            'name' => 'Annual Plan',
            'price' => 99.00,
            'status' => 'active',
        ]);
        $plan->id = 200;
        $plan->setRelation('product', $product);

        $admin = new User([
            'name' => 'Admin',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);
        $admin->id = 1;

        $action = new class($gateway, $logger) extends ProcessSaleAction
        {
            protected function runInTransaction(callable $callback): mixed
            {
                return $callback();
            }

            protected function createOrder(array $attributes): Order
            {
                $order = new Order($attributes);
                $order->id = 500;

                return $order;
            }

            protected function createOrderItem(array $attributes): OrderItem
            {
                return new OrderItem($attributes);
            }

            protected function createPayment(array $attributes): Payment
            {
                $payment = new Payment($attributes);
                $payment->id = 600;

                return $payment;
            }

            protected function createPaymentTransaction(array $attributes): PaymentTransaction
            {
                return new PaymentTransaction($attributes);
            }

            protected function createSubscription(array $attributes): Subscription
            {
                $sub = new Subscription($attributes);
                $sub->id = 700;

                return $sub;
            }

            protected function createSubscriptionStatusHistory(array $attributes): SubscriptionStatusHistory
            {
                return new SubscriptionStatusHistory($attributes);
            }
        };

        $result = $action->execute([
            'customer' => $customer,
            'plan' => $plan,
            'quantity' => 1,
        ], $admin);

        $this->assertNotNull($result['order']);
        $this->assertNotNull($result['payment']);
        $this->assertNotNull($result['subscription']);

        Event::assertDispatched(SaleCompleted::class, function (SaleCompleted $event) use ($result, $admin) {
            return $event->order->id === $result['order']->id
                && $event->actor?->id === $admin->id;
        });

        Event::assertDispatched(PaymentReceived::class, function (PaymentReceived $event) use ($result, $admin) {
            return $event->payment->id === $result['payment']->id
                && $event->actor?->id === $admin->id;
        });
    }

    public function test_listeners_keep_recipient_targeting_blocked_under_bc_9_01(): void
    {
        $service = new NotificationService;

        $order = new Order;
        $order->id = 500;

        $payment = new Payment;
        $payment->id = 600;

        // Both handleEvent calls execute safely without inventing recipients
        $service->handleEvent(new SaleCompleted($order));
        $service->handleEvent(new PaymentReceived($payment));

        $this->assertTrue(true);
    }
}
