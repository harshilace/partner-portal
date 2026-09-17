<?php

namespace Tests\Feature\Leads;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Leads\Lead;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class LeadManualCreatePolicyTest extends TestCase
{
    public function test_non_admin_roles_cannot_manually_create_leads(): void
    {
        $mainUser = new User(['id' => 2, 'role' => Role::MAIN_PARTNER->value]);
        $subUser = new User(['id' => 3, 'role' => Role::SUB_PARTNER->value]);
        $partnerUser = new User(['id' => 4, 'role' => Role::PARTNER_USER->value]);

        $this->assertFalse(Gate::forUser($mainUser)->allows('create', Lead::class));
        $this->assertFalse(Gate::forUser($subUser)->allows('create', Lead::class));
        $this->assertFalse(Gate::forUser($partnerUser)->allows('create', Lead::class));
    }

    public function test_admin_is_authorized_to_create_lead_via_before(): void
    {
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value]);

        $this->assertTrue(Gate::forUser($admin)->allows('create', Lead::class));
    }
}
