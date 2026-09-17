<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Leads\Lead;
use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Domain\Payments\Order;
use App\Domain\Payments\OrderItem;
use App\Domain\Payments\Payment;
use App\Domain\Payments\PaymentTransaction;
use App\Domain\Products\ProductPlan;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionStatusHistory;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ProcessSaleAction
{
    public function __construct(
        protected PaymentGatewayInterface $paymentGateway,
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Execute sales flow according to Master Section 14 and Section 17:
     * Lead -> Purchase -> Payment -> Order Created -> Customer -> Subscription Activated
     *
     * @param  array{
     *     customer: Customer,
     *     plan: ProductPlan,
     *     lead?: Lead|null,
     *     quantity?: int,
     *     payment_method?: string|null,
     *     payment_options?: array<string, mixed>,
     *     discounts?: mixed,
     *     custom_pricing?: mixed,
     *     commercial_rules?: mixed,
     *     starts_at?: Carbon|string|null,
     *     expires_at?: Carbon|string|null
     * }  $payload
     * @return array{order: Order, payment: Payment, subscription: Subscription}
     */
    public function execute(array $payload, ?User $actor = null): array
    {
        // Block unconfirmed discount rules
        if (isset($payload['discounts']) || isset($payload['discount']) || isset($payload['coupon'])) {
            throw new RuntimeException('Discount rules are pending business confirmation.');
        }

        // Block unconfirmed exact pricing rules
        if (isset($payload['custom_pricing']) || isset($payload['dynamic_pricing']) || isset($payload['tax_calculation'])) {
            throw new RuntimeException('Exact product pricing rules are pending business confirmation.');
        }

        // Block unconfirmed subscription commercial rules
        if (isset($payload['commercial_rules']) || isset($payload['proration']) || isset($payload['upgrade_downgrade'])) {
            throw new RuntimeException('Subscription commercial rules are pending business confirmation.');
        }

        $customer = $payload['customer'];
        $plan = $payload['plan'];
        $lead = $payload['lead'] ?? null;
        $quantity = (int) ($payload['quantity'] ?? 1);

        if ($quantity < 1) {
            throw new DomainException('Quantity must be at least 1.');
        }

        $product = $plan->product;
        if (! $product) {
            throw new DomainException('Product plan is not associated with a valid product.');
        }

        // Determine ownership snapshots from Lead or Customer attribution
        $partnerId = $lead?->partner_id ?? $customer->current_partner_id;
        $subPartnerId = $lead?->sub_partner_id ?? $customer->current_sub_partner_id;

        $unitPrice = (float) $plan->price;
        $totalAmount = $unitPrice * $quantity;

        return $this->runInTransaction(function () use ($customer, $product, $plan, $lead, $partnerId, $subPartnerId, $quantity, $unitPrice, $totalAmount, $payload, $actor) {
            // Step 1: Payment via abstraction (Section 17)
            $paymentResult = $this->paymentGateway->charge(
                $customer,
                $totalAmount,
                $payload['payment_options'] ?? []
            );

            // Step 2: Order Created (Section 14 & 17)
            $order = $this->createOrder([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer->id,
                'partner_id' => $partnerId,
                'sub_partner_id' => $subPartnerId,
                'lead_id' => $lead?->id,
                'total_amount' => $totalAmount,
                'ordered_at' => Carbon::now(),
            ]);

            // Step 3: Order Item Created
            $orderItem = $this->createOrderItem([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_plan_id' => $plan->id,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'total_price' => $totalAmount,
            ]);

            // Step 4: Payment Record Created
            $payment = $this->createPayment([
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'amount' => $totalAmount,
                'payment_method' => $payload['payment_method'] ?? null,
                'paid_at' => Carbon::now(),
            ]);

            // Step 5: Payment Transaction Created
            $transaction = $this->createPaymentTransaction([
                'payment_id' => $payment->id,
                'gateway_name' => $paymentResult['gateway_name'] ?? null,
                'transaction_reference' => $paymentResult['transaction_reference'] ?? null,
                'amount' => $paymentResult['amount'] ?? $totalAmount,
                'status' => $paymentResult['status'] ?? 'initiated',
                'gateway_response' => $paymentResult['gateway_response'] ?? null,
                'transacted_at' => $paymentResult['transacted_at'] ?? Carbon::now(),
            ]);

            // Step 6: Subscription Activated (Section 14 & 18)
            $subscription = $this->createSubscription([
                'subscription_number' => $this->generateSubscriptionNumber(),
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_plan_id' => $plan->id,
                'partner_id' => $partnerId,
                'sub_partner_id' => $subPartnerId,
                'starts_at' => $payload['starts_at'] ?? Carbon::now(),
                'expires_at' => $payload['expires_at'] ?? null,
            ]);

            // Step 7: Subscription Status History
            $this->createSubscriptionStatusHistory([
                'subscription_id' => $subscription->id,
                'from_status' => null,
                'to_status' => $subscription->status ?? 'active',
                'changed_by_user_id' => $actor?->id,
                'reason' => 'Initial purchase',
            ]);

            // Step 8: Audit Log
            $this->auditLogger->log(
                'sale.completed',
                $actor,
                $order,
                null,
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_id' => $customer->id,
                    'partner_id' => $partnerId,
                    'sub_partner_id' => $subPartnerId,
                    'total_amount' => $totalAmount,
                    'subscription_id' => $subscription->id,
                ]
            );

            return [
                'order' => $order,
                'payment' => $payment,
                'subscription' => $subscription,
            ];
        });
    }

    protected function runInTransaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    protected function generateOrderNumber(): string
    {
        return 'ORD-'.Carbon::now()->format('YmdHis').'-'.Str::upper(Str::random(6));
    }

    protected function generateSubscriptionNumber(): string
    {
        return 'SUB-'.Carbon::now()->format('YmdHis').'-'.Str::upper(Str::random(6));
    }

    protected function createOrder(array $attributes): Order
    {
        return Order::create($attributes);
    }

    protected function createOrderItem(array $attributes): OrderItem
    {
        return OrderItem::create($attributes);
    }

    protected function createPayment(array $attributes): Payment
    {
        return Payment::create($attributes);
    }

    protected function createPaymentTransaction(array $attributes): PaymentTransaction
    {
        return PaymentTransaction::create($attributes);
    }

    protected function createSubscription(array $attributes): Subscription
    {
        return Subscription::create($attributes);
    }

    protected function createSubscriptionStatusHistory(array $attributes): SubscriptionStatusHistory
    {
        return SubscriptionStatusHistory::create($attributes);
    }
}
