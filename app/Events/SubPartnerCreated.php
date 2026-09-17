<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubPartnerCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly mixed $subPartner,
        public readonly mixed $actor = null
    ) {}
}
