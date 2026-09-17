<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly mixed $payment = null,
        public readonly array $data = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Failed')
            ->line('A payment attempt has failed.');
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => 'payment_failed',
            'title' => 'Payment Failed',
            'payment_id' => $this->payment?->id ?? $this->data['payment_id'] ?? null,
        ], $this->data);
    }
}
