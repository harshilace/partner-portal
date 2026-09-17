<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCustomerNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly mixed $customer = null,
        public readonly array $data = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Customer')
            ->line('A new customer has been recorded.');
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => 'new_customer',
            'title' => 'New Customer',
            'customer_id' => $this->customer?->id ?? $this->data['customer_id'] ?? null,
        ], $this->data);
    }
}
