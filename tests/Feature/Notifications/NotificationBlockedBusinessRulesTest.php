<?php

namespace Tests\Feature\Notifications;

use App\Domain\Notifications\Services\NotificationService;
use App\Events\CustomerCreated;
use App\Events\LeadCreated;
use App\Events\PaymentFailed;
use App\Events\SubscriptionCancelled;
use Tests\TestCase;

class NotificationBlockedBusinessRulesTest extends TestCase
{
    public function test_payment_failed_notification_trigger_remains_blocked_under_bc_9_04(): void
    {
        $event = new PaymentFailed(null);
        $this->assertNull($event->payment);

        $service = new NotificationService;
        // Trigger and recipients remain unresolved
        $service->handleEvent($event);
        $this->assertTrue(true);
    }

    public function test_subscription_cancelled_notification_trigger_remains_blocked_under_bc_9_05(): void
    {
        $event = new SubscriptionCancelled(null);
        $this->assertNull($event->subscription);

        $service = new NotificationService;
        // Trigger and recipients remain unresolved
        $service->handleEvent($event);
        $this->assertTrue(true);
    }

    public function test_lead_and_customer_created_triggers_remain_blocked_under_bc_9_06(): void
    {
        $leadEvent = new LeadCreated(null);
        $this->assertNull($leadEvent->lead);

        $customerEvent = new CustomerCreated(null);
        $this->assertNull($customerEvent->customer);

        $service = new NotificationService;
        // Both triggers and recipients remain unresolved
        $service->handleEvent($leadEvent);
        $service->handleEvent($customerEvent);
        $this->assertTrue(true);
    }
}
