<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SaleCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly mixed $order = null,
        public readonly array $data = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Sale Completed')
            ->line('A sale has been completed.');
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => 'sale_completed',
            'title' => 'Sale Completed',
            'order_id' => $this->order?->id ?? $this->data['order_id'] ?? null,
        ], $this->data);
    }
}
