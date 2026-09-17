<?php

namespace App\Domain\Subscriptions\Actions;

use App\Domain\Authentication\User;
use RuntimeException;

class CreateAutoDebitMandateAction
{
    /**
     * Mandate creation authorization and workflow are not defined in Master Project Documentation v1.0.
     * This operation is strictly blocked pending business confirmation.
     *
     * @throws RuntimeException
     */
    public function execute(array $data = [], ?User $actor = null): never
    {
        throw new RuntimeException('Auto-debit mandate creation authorization and workflow are pending business confirmation.');
    }
}
