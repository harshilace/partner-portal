<?php

namespace App\Domain\Payments\Contracts;

use App\Domain\Customers\Customer;
use App\Domain\Payments\Payment;
use Illuminate\Support\Carbon;

interface PaymentGatewayInterface
{
    /**
     * Process payment abstraction (Section 17).
     * Does not assume concrete gateway provider or status state machine.
     *
     * @param  array<string, mixed>  $options
     * @return array{
     *     gateway_name: string,
     *     transaction_reference: string,
     *     amount: string|float,
     *     status: string,
     *     gateway_response: array<string, mixed>|null,
     *     transacted_at: Carbon
     * }
     */
    public function charge(Customer $customer, string|float $amount, array $options = []): array;
}
