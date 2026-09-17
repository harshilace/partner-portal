<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly mixed $subscription = null,
        public readonly array $data = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Subscription Cancelled')
            ->line('A subscription has been cancelled.');
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => 'subscription_cancelled',
            'title' => 'Subscription Cancelled',
            'subscription_id' => $this->subscription?->id ?? $this->data['subscription_id'] ?? null,
        ], $this->data);
    }
}
