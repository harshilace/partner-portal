<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification
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
            ->subject('Payment Received')
            ->line('A payment has been received.');
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => 'payment_received',
            'title' => 'Payment Received',
            'payment_id' => $this->payment?->id ?? $this->data['payment_id'] ?? null,
        ], $this->data);
    }
}
