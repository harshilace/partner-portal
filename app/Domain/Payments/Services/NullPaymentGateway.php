<?php

namespace App\Domain\Payments\Services;

use App\Domain\Customers\Customer;
use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NullPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Test / development implementation of payment gateway abstraction.
     * Uses default schema status ('initiated') to avoid inventing undocumented statuses.
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
    public function charge(Customer $customer, string|float $amount, array $options = []): array
    {
        return [
            'gateway_name' => $options['gateway_name'] ?? 'null_gateway',
            'transaction_reference' => $options['transaction_reference'] ?? 'TXN-'.Str::upper(Str::random(12)),
            'amount' => $amount,
            'status' => $options['status'] ?? 'initiated',
            'gateway_response' => ['simulated' => true],
            'transacted_at' => Carbon::now(),
        ];
    }
}
