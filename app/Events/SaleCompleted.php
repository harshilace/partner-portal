<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SaleCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly mixed $order,
        public readonly mixed $actor = null
    ) {}
}
