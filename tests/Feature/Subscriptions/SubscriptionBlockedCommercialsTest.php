<?php

namespace Tests\Feature\Subscriptions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Payments\Actions\ProcessSaleAction;
use App\Domain\Payments\Services\NullPaymentGateway;
use App\Domain\Products\Product;
use App\Domain\Products\ProductPlan;
use RuntimeException;
use Tests\TestCase;

class SubscriptionBlockedCommercialsTest extends TestCase
{
    protected ProcessSaleAction $action;

    protected User $admin;

    protected Customer $customer;

    protected ProductPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $gateway = new NullPaymentGateway;
        $logger = $this->createMock(AuditLogger::class);

        $this->action = new ProcessSaleAction($gateway, $logger);
        $this->admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $this->customer = new Customer(['id' => 1, 'current_partner_id' => 2]);
        $product = new Product(['id' => 10, 'is_active' => true]);
        $this->plan = new ProductPlan(['id' => 100, 'product_id' => 10, 'price' => '50.00', 'is_active' => true]);
        $this->plan->setRelation('product', $product);
    }

    public function test_discount_attempts_are_blocked_pending_confirmation(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Discount rules are pending business confirmation.');

        $this->action->execute([
            'customer' => $this->customer,
            'plan' => $this->plan,
            'discounts' => ['code' => 'SAVE20'],
        ], $this->admin);
    }

    public function test_dynamic_pricing_attempts_are_blocked_pending_confirmation(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Exact product pricing rules are pending business confirmation.');

        $this->action->execute([
            'customer' => $this->customer,
            'plan' => $this->plan,
            'custom_pricing' => ['tiered_rate' => 40.00],
        ], $this->admin);
    }

    public function test_subscription_commercial_rules_are_blocked_pending_confirmation(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Subscription commercial rules are pending business confirmation.');

        $this->action->execute([
            'customer' => $this->customer,
            'plan' => $this->plan,
            'commercial_rules' => ['prorate' => true],
        ], $this->admin);
    }
}
