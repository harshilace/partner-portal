<?php

namespace Tests\Unit\Notifications;

use App\Domain\Authentication\User;
use App\Notifications\AutoDebitStoppedNotification;
use App\Notifications\NewCustomerNotification;
use App\Notifications\NewLeadNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentReceivedNotification;
use App\Notifications\RenewalDueNotification;
use App\Notifications\SaleCompletedNotification;
use App\Notifications\SubPartnerCreatedNotification;
use App\Notifications\SubscriptionCancelledNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class NotificationChannelsTest extends TestCase
{
    public function test_all_nine_notification_classes_return_mail_and_database_channels(): void
    {
        $classes = [
            new NewLeadNotification,
            new NewCustomerNotification,
            new SaleCompletedNotification,
            new PaymentReceivedNotification,
            new RenewalDueNotification,
            new PaymentFailedNotification,
            new AutoDebitStoppedNotification,
            new SubscriptionCancelledNotification,
            new SubPartnerCreatedNotification,
        ];

        $user = new User;

        foreach ($classes as $notification) {
            $channels = $notification->via($user);
            $this->assertEquals(['mail', 'database'], $channels);

            $mail = $notification->toMail($user);
            $this->assertInstanceOf(MailMessage::class, $mail);
            $this->assertNotEmpty($mail->subject);
            $this->assertNotEmpty($mail->introLines);

            $payload = $notification->toArray($user);
            $this->assertIsArray($payload);
            $this->assertArrayHasKey('type', $payload);
            $this->assertArrayHasKey('title', $payload);
        }
    }

    public function test_notification_to_array_contains_expected_domain_identifiers(): void
    {
        $user = new User;

        $leadNotification = new NewLeadNotification(null, ['lead_id' => 123]);
        $this->assertEquals('new_lead', $leadNotification->toArray($user)['type']);
        $this->assertEquals(123, $leadNotification->toArray($user)['lead_id']);

        $renewalNotification = new RenewalDueNotification(null, '15d', ['renewal_id' => 456]);
        $this->assertEquals('renewal_due', $renewalNotification->toArray($user)['type']);
        $this->assertEquals('15d', $renewalNotification->toArray($user)['milestone']);
        $this->assertEquals(456, $renewalNotification->toArray($user)['renewal_id']);

        $saleNotification = new SaleCompletedNotification(null, ['order_id' => 789]);
        $this->assertEquals('sale_completed', $saleNotification->toArray($user)['type']);
        $this->assertEquals(789, $saleNotification->toArray($user)['order_id']);
    }
}
