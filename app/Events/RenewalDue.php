<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RenewalDue
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly mixed $renewal,
        public readonly string $milestone,
        public readonly mixed $actor = null
    ) {}
}
