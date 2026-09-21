<?php

namespace Tests\Feature\Hardening;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Customers\Actions\ChangeCustomerPartnerMappingAction;
use App\Domain\Customers\Customer;
use App\Domain\Partners\Partner;
use App\Domain\Payments\Actions\ProcessSaleAction;
use App\Domain\Payments\Order;
use App\Domain\Payments\OrderItem;
use App\Domain\Payments\Payment;
use App\Domain\Payments\Services\NullPaymentGateway;
use App\Domain\Products\Product;
use App\Domain\Products\ProductPlan;
use App\Events\PaymentReceived;
use App\Events\SaleCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class TransactionIntegrityHardeningTest extends TestCase
{
    /**
     * ProcessSaleAction runs structurally within a transaction; an exception halts execution
     * and prevents downstream event dispatching.
     */
    public function test_sale_process_action_aborts_transactionally_and_suppresses_events_on_step_failure(): void
    {
        Event::fake([SaleCompleted::class, PaymentReceived::class]);

        $gateway = new NullPaymentGateway;
        $logger = $this->createMock(AuditLogger::class);

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $customer = new Customer(['current_partner_id' => 1, 'current_sub_partner_id' => 10]);
        $customer->id = 50;

        $product = new Product(['is_active' => true]);
        $product->id = 5;

        $plan = new ProductPlan(['product_id' => 5, 'price' => '100.00', 'is_active' => true]);
        $plan->id = 55;
        $plan->setRelation('product', $product);

        $action = new class($gateway, $logger) extends ProcessSaleAction
        {
            public bool $transactionWrapped = false;

            protected function runInTransaction(callable $callback): mixed
            {
                $this->transactionWrapped = true;

                return $callback();
            }

            protected function createOrder(array $attributes): Order
            {
                $order = new Order($attributes);
                $order->id = 100;

                return $order;
            }

            protected function createOrderItem(array $attributes): OrderItem
            {
                $item = new OrderItem($attributes);
                $item->id = 101;

                return $item;
            }

            protected function createPayment(array $attributes): Payment
            {
                // Simulate mid-transaction failure during payment creation
                throw new RuntimeException('Payment record creation failed unexpectedly.');
            }
        };

        try {
            $action->execute([
                'customer' => $customer,
                'plan' => $plan,
                'quantity' => 1,
                'payment_method' => 'card',
            ], $admin);
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $e) {
            $this->assertEquals('Payment record creation failed unexpectedly.', $e->getMessage());
        }

        $this->assertTrue($action->transactionWrapped, 'Sale execution was not wrapped inside runInTransaction abstraction.');
        Event::assertNotDispatched(SaleCompleted::class);
        Event::assertNotDispatched(PaymentReceived::class);
    }

    /**
     * ChangeCustomerPartnerMappingAction locks the customer and wraps multi-step attribution
     * updates structurally in a transaction.
     */
    public function test_customer_mapping_action_locks_customer_and_aborts_without_audit_on_failure(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->never())->method('log');

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $customer = new Customer(['current_partner_id' => 1, 'current_sub_partner_id' => 10]);
        $customer->id = 50;

        $newPartner = new Partner(['type' => 'main', 'status' => 'active']);
        $newPartner->id = 2;

        $action = new class($logger) extends ChangeCustomerPartnerMappingAction
        {
            public bool $customerLocked = false;

            protected function lockCustomer(int|string $customerId): ?Customer
            {
                $this->customerLocked = true;

                return new Customer(['id' => $customerId]);
            }

            protected function closeOpenAttributions(int|string $customerId, mixed $timestamp): int
            {
                // Simulate failure on closing old attribution
                throw new RuntimeException('Attribution closure failed.');
            }
        };

        try {
            $action->execute($customer, $newPartner, null, $admin);
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $e) {
            $this->assertEquals('Attribution closure failed.', $e->getMessage());
        }

        $this->assertTrue($action->customerLocked, 'Customer was not locked prior to attribution modification.');
    }
}
