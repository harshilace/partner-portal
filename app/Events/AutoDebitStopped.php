<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AutoDebitStopped
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly mixed $mandate,
        public readonly mixed $actor = null
    ) {}
}
