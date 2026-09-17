<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly mixed $subscription = null,
        public readonly mixed $actor = null
    ) {}
}
