<?php

namespace Tests\Feature\Renewals;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Renewals\Actions\ProcessRenewalAction;
use App\Domain\Renewals\Renewal;
use App\Domain\Subscriptions\Actions\CreateAutoDebitMandateAction;
use App\Domain\Subscriptions\AutoDebitMandate;
use Illuminate\Support\Facades\Gate;
use RuntimeException;
use Tests\TestCase;

class RenewalBlockedOperationsTest extends TestCase
{
    public function test_renewal_processing_is_blocked_for_all_roles(): void
    {
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);
        $mainPartner = new User(['id' => 2, 'role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $subPartner = new User(['id' => 3, 'role' => Role::SUB_PARTNER->value, 'status' => 'active']);

        $renewal = new Renewal(['id' => 10, 'status' => 'pending']);

        // Blocked at policy level for all roles
        $this->assertFalse(Gate::forUser($admin)->allows('process', $renewal));
        $this->assertFalse(Gate::forUser($mainPartner)->allows('process', $renewal));
        $this->assertFalse(Gate::forUser($subPartner)->allows('process', $renewal));

        // Blocked at domain action level
        $action = new ProcessRenewalAction;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Renewal processing authorization and commercial rules are pending business confirmation.');

        $action->execute($renewal, [], $admin);
    }

    public function test_mandate_creation_is_blocked_for_all_roles(): void
    {
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);
        $mainPartner = new User(['id' => 2, 'role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $subPartner = new User(['id' => 3, 'role' => Role::SUB_PARTNER->value, 'status' => 'active']);

        // Blocked at policy level for all roles
        $this->assertFalse(Gate::forUser($admin)->allows('create', AutoDebitMandate::class));
        $this->assertFalse(Gate::forUser($mainPartner)->allows('create', AutoDebitMandate::class));
        $this->assertFalse(Gate::forUser($subPartner)->allows('create', AutoDebitMandate::class));

        // Blocked at domain action level
        $action = new CreateAutoDebitMandateAction;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Auto-debit mandate creation authorization and workflow are pending business confirmation.');

        $action->execute([], $admin);
    }
}
