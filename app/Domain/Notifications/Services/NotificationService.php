<?php

namespace App\Domain\Notifications\Services;

use App\Domain\Authentication\User;
use App\Domain\Renewals\Renewal;
use App\Events\RenewalDue;
use App\Notifications\RenewalDueNotification;
use Illuminate\Notifications\Notification;

class NotificationService
{
    /**
     * Dispatch notification to a set of notifiable recipients.
     *
     * @param  iterable<User>  $recipients
     */
    public function dispatch(Notification $notification, iterable $recipients): void
    {
        foreach ($recipients as $recipient) {
            $recipient->notify($notification);
        }
    }

    /**
     * Handle domain event and resolve documented recipients.
     * Recipient resolution is strictly server-side based on domain models.
     * Unconfirmed recipients (BC-9-01) remain blocked (empty recipient list).
     */
    public function handleEvent(object $event): void
    {
        if ($event instanceof RenewalDue) {
            $this->handleRenewalDue($event);

            return;
        }

        // For the other 8 event types:
        // BC-9-01: Recipient mapping is unresolved by Master Project Documentation.
        // No recipients are invented.
    }

    /**
     * Handle RenewalDue milestone event.
     * Master §19 documents:
     * - 15 days → Partner + Customer
     * - 7 days → Partner + Customer
     * - 30 days → Optional reminder (recipients not specified)
     * - 1 day → Final reminder (recipients not specified)
     */
    protected function handleRenewalDue(RenewalDue $event): void
    {
        $milestone = $event->milestone;

        if (! in_array($milestone, ['15d', '7d'], true)) {
            // 30d and 1d milestone recipients remain unresolved (BC-9-01)
            return;
        }

        $renewal = $event->renewal;
        $partnerUsers = $this->resolvePartnerUsersForRenewal($renewal);

        if (! empty($partnerUsers)) {
            $notification = new RenewalDueNotification($renewal, $milestone);
            $this->dispatch($notification, $partnerUsers);
        }

        // Customer delivery mechanism is stubbed pending BC-9-03
        // In MVP, customer delivery path is blocked because Customer Portal is Phase 2
        // and exact delivery mechanism (e.g. direct email vs portal) is not documented.
        $this->stubCustomerDelivery($renewal, $milestone);
    }

    /**
     * Resolve partner users for renewal.
     * Master documents: Partner + Customer.
     * We do not infer whether Partner means one or multiple users;
     * we retrieve the users associated with the partner entity without arbitrary assumptions.
     *
     * @return array<User>
     */
    protected function resolvePartnerUsersForRenewal(mixed $renewal): array
    {
        if (! ($renewal instanceof Renewal)) {
            return [];
        }

        $partner = $renewal->partner ?? $renewal->subscription?->partner;

        if (! $partner) {
            return [];
        }

        if ($partner->relationLoaded('users')) {
            return $partner->users->where('status', 'active')->all();
        }

        return $partner->users()->where('status', 'active')->get()->all();
    }

    /**
     * Customer delivery mechanism is stubbed pending BC-9-03.
     */
    protected function stubCustomerDelivery(mixed $renewal, string $milestone): void
    {
        // Blocked pending BC-9-03: Customer delivery mechanism in MVP.
    }
}
