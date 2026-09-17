<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly mixed $customer,
        public readonly mixed $actor = null
    ) {}
}
