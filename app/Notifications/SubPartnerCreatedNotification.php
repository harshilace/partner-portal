<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubPartnerCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly mixed $subPartner = null,
        public readonly array $data = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Sub-Partner Created')
            ->line('A new sub-partner has been created.');
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => 'sub_partner_created',
            'title' => 'Sub-Partner Created',
            'sub_partner_id' => $this->subPartner?->id ?? $this->data['sub_partner_id'] ?? null,
        ], $this->data);
    }
}
