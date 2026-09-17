<?php

namespace App\Domain\Renewals\Actions;

use App\Domain\Authentication\User;
use App\Domain\Renewals\Renewal;
use RuntimeException;

class ProcessRenewalAction
{
    /**
     * Renewal processing authorization, commercial pricing rules, and expiry calculation formulas
     * are marked as NEEDS BUSINESS CONFIRMATION in Master Project Documentation v1.0.
     * This operation is strictly blocked until confirmed.
     *
     * @throws RuntimeException
     */
    public function execute(Renewal $renewal, array $data = [], ?User $actor = null): never
    {
        throw new RuntimeException('Renewal processing authorization and commercial rules are pending business confirmation.');
    }
}
