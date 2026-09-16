<?php

namespace Tests\Unit;

use App\Domain\Audit\AuditLog;
use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Customers\CustomerPartnerAttribution;
use App\Domain\Leads\Lead;
use App\Domain\Leads\LeadFollowUp;
use App\Domain\Leads\LeadStatusHistory;
use App\Domain\Notifications\Notification;
use App\Domain\Partners\Partner;
use App\Domain\Partners\PartnerUser;
use App\Domain\Payments\Order;
use App\Domain\Payments\OrderItem;
use App\Domain\Payments\Payment;
use App\Domain\Payments\PaymentTransaction;
use App\Domain\Products\Product;
use App\Domain\Products\ProductPlan;
use App\Domain\Referrals\ReferralCode;
use App\Domain\Renewals\Renewal;
use App\Domain\Subscriptions\AutoDebitEvent;
use App\Domain\Subscriptions\AutoDebitMandate;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionStatusHistory;
use Tests\TestCase;

class Phase1DatabaseTest extends TestCase
{
    public function test_all_22_migrations_exist_in_exact_order(): void
    {
        $expectedOrder = [
            'create_users_table.php',
            'create_partners_table.php',
            'create_partner_users_table.php',
            'create_products_table.php',
            'create_product_plans_table.php',
            'create_referral_codes_table.php',
            'create_customers_table.php',
            'create_customer_partner_attributions_table.php',
            'create_leads_table.php',
            'create_lead_status_histories_table.php',
            'create_lead_follow_ups_table.php',
            'create_orders_table.php',
            'create_order_items_table.php',
            'create_payments_table.php',
            'create_payment_transactions_table.php',
            'create_subscriptions_table.php',
            'create_subscription_status_histories_table.php',
            'create_auto_debit_mandates_table.php',
            'create_auto_debit_events_table.php',
            'create_renewals_table.php',
            'create_notifications_table.php',
            'create_audit_logs_table.php',
        ];

        $files = scandir(database_path('migrations'));
        $migrationFiles = array_values(array_filter($files, fn ($f) => str_ends_with($f, '.php')));
        sort($migrationFiles);

        $this->assertCount(22, $migrationFiles, 'Expected exactly 22 migration files');

        foreach ($expectedOrder as $index => $suffix) {
            $this->assertTrue(
                str_ends_with($migrationFiles[$index], $suffix),
                "Migration at index {$index} should end with {$suffix}, found {$migrationFiles[$index]}"
            );
        }
    }

    public function test_all_22_domain_models_are_instantiable(): void
    {
        $models = [
            User::class,
            Partner::class,
            PartnerUser::class,
            Product::class,
            ProductPlan::class,
            ReferralCode::class,
            Customer::class,
            CustomerPartnerAttribution::class,
            Lead::class,
            LeadStatusHistory::class,
            LeadFollowUp::class,
            Order::class,
            OrderItem::class,
            Payment::class,
            PaymentTransaction::class,
            Subscription::class,
            SubscriptionStatusHistory::class,
            AutoDebitMandate::class,
            AutoDebitEvent::class,
            Renewal::class,
            Notification::class,
            AuditLog::class,
        ];

        foreach ($models as $modelClass) {
            $instance = new $modelClass;
            $this->assertInstanceOf($modelClass, $instance);
        }
    }

    public function test_models_have_expected_relationship_methods(): void
    {
        $partner = new Partner;
        $this->assertTrue(method_exists($partner, 'subPartners'));
        $this->assertTrue(method_exists($partner, 'users'));
        $this->assertTrue(method_exists($partner, 'orders'));
        $this->assertTrue(method_exists($partner, 'subscriptions'));

        $customer = new Customer;
        $this->assertTrue(method_exists($customer, 'currentPartner'));
        $this->assertTrue(method_exists($customer, 'attributions'));
        $this->assertTrue(method_exists($customer, 'orders'));

        $order = new Order;
        $this->assertTrue(method_exists($order, 'customer'));
        $this->assertTrue(method_exists($order, 'items'));
        $this->assertTrue(method_exists($order, 'payments'));
        $this->assertTrue(method_exists($order, 'partner'));

        $subscription = new Subscription;
        $this->assertTrue(method_exists($subscription, 'customer'));
        $this->assertTrue(method_exists($subscription, 'product'));
        $this->assertTrue(method_exists($subscription, 'productPlan'));
        $this->assertTrue(method_exists($subscription, 'partner'));
    }
}
