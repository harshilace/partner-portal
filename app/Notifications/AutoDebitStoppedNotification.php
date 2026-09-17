<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AutoDebitStoppedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly mixed $mandate = null,
        public readonly array $data = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Auto-Debit Stopped')
            ->line('An auto-debit mandate has been stopped.');
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => 'auto_debit_stopped',
            'title' => 'Auto-Debit Stopped',
            'mandate_id' => $this->mandate?->id ?? $this->data['mandate_id'] ?? null,
        ], $this->data);
    }
}
