<?php

namespace Tests\Feature\Renewals;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use App\Domain\Renewals\Actions\CreateRenewalAction;
use App\Domain\Renewals\Renewal;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RenewalOwnershipAndScopingTest extends TestCase
{
    public function test_create_renewal_snapshots_historical_partner_ownership(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with($this->equalTo('renewal.created'));

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $subscription = new Subscription([
            'customer_id' => 7,
            'partner_id' => 2,
            'sub_partner_id' => 20,
        ]);
        $subscription->id = 55;

        $action = new class($logger) extends CreateRenewalAction
        {
            public ?Renewal $createdRecord = null;

            protected function runInTransaction(callable $callback): mixed
            {
                return $callback();
            }

            protected function createRenewal(array $attributes): Renewal
            {
                $this->createdRecord = new Renewal($attributes);

                return $this->createdRecord;
            }
        };

        $result = $action->execute($subscription, '2026-11-15', $admin);

        $this->assertEquals(55, $result->subscription_id);
        $this->assertEquals(7, $result->customer_id);
        $this->assertEquals(2, $result->partner_id);
        $this->assertEquals(20, $result->sub_partner_id);
        $this->assertEquals('2026-11-15', $result->due_date->toDateString());
        $this->assertEquals('pending', $result->status);
    }

    public function test_sub_partner_can_only_view_own_renewals(): void
    {
        $subPartner = new Partner(['type' => 'sub', 'status' => 'active']);
        $subPartner->id = 20;

        $subUser = new class(['id' => 3, 'role' => Role::SUB_PARTNER->value, 'status' => 'active']) extends User
        {
            public ?Partner $mockPartner = null;

            public function isSubPartner(): bool
            {
                return true;
            }

            public function isMainPartner(): bool
            {
                return false;
            }

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $subUser->mockPartner = $subPartner;

        $ownRenewal = new Renewal(['id' => 1, 'partner_id' => 2, 'sub_partner_id' => 20]);
        $otherRenewal = new Renewal(['id' => 2, 'partner_id' => 2, 'sub_partner_id' => 21]);

        $this->assertTrue(Gate::forUser($subUser)->allows('view', $ownRenewal));
        $this->assertFalse(Gate::forUser($subUser)->allows('view', $otherRenewal));
    }

    public function test_main_partner_access_to_sub_partner_renewal_rows_is_blocked(): void
    {
        $mainPartner = new Partner(['type' => 'main', 'status' => 'active']);
        $mainPartner->id = 2;

        $mainUser = new class(['id' => 2, 'role' => Role::MAIN_PARTNER->value, 'status' => 'active']) extends User
        {
            public ?Partner $mockPartner = null;

            public function isSubPartner(): bool
            {
                return false;
            }

            public function isMainPartner(): bool
            {
                return true;
            }

            public function partner(): ?Partner
            {
                return $this->mockPartner;
            }
        };
        $mainUser->mockPartner = $mainPartner;

        // Direct renewal under Main Partner
        $directRenewal = new Renewal(['id' => 1, 'partner_id' => 2, 'sub_partner_id' => null]);
        // Renewal under Sub-Partner
        $subPartnerRenewal = new Renewal(['id' => 2, 'partner_id' => 2, 'sub_partner_id' => 20]);

        $this->assertTrue(Gate::forUser($mainUser)->allows('view', $directRenewal));
        // Main Partner access to individual Sub-Partner renewal rows is blocked
        $this->assertFalse(Gate::forUser($mainUser)->allows('view', $subPartnerRenewal));
    }

    public function test_admin_has_unrestricted_view_authority_on_renewals(): void
    {
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);
        $renewal = new Renewal(['id' => 1, 'partner_id' => 2, 'sub_partner_id' => 20]);

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Renewal::class));
        $this->assertTrue(Gate::forUser($admin)->allows('view', $renewal));
    }
}
