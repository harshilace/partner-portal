<?php

namespace App\Listeners;

use App\Domain\Notifications\Services\NotificationService;

class SendNotificationListener
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function handle(object $event): void
    {
        $this->notificationService->handleEvent($event);
    }
}
