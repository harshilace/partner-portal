<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RenewalDueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly mixed $renewal = null,
        public readonly string $milestone = '',
        public readonly array $data = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Renewal Due')
            ->line('A subscription renewal is due.');
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => 'renewal_due',
            'title' => 'Renewal Due',
            'renewal_id' => $this->renewal?->id ?? $this->data['renewal_id'] ?? null,
            'milestone' => $this->milestone,
        ], $this->data);
    }
}
