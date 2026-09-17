<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly mixed $lead = null,
        public readonly array $data = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Lead')
            ->line('A new lead has been recorded.');
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => 'new_lead',
            'title' => 'New Lead',
            'lead_id' => $this->lead?->id ?? $this->data['lead_id'] ?? null,
        ], $this->data);
    }
}
