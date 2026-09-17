<?php

namespace Tests\Feature\Partners;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Actions\CreatePartnerAction;
use App\Domain\Partners\Actions\DeactivatePartnerAction;
use App\Domain\Partners\Actions\UpdatePartnerAction;
use App\Domain\Partners\Partner;
use App\Policies\PartnerPolicy;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class PartnerManagementTest extends TestCase
{
    protected PartnerPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PartnerPolicy;
    }

    public function test_partner_model_helpers_and_scope(): void
    {
        $mainPartner = new Partner(['type' => 'main', 'status' => 'active']);
        $this->assertTrue($mainPartner->isMain());
        $this->assertFalse($mainPartner->isSub());
        $this->assertTrue($mainPartner->isActive());

        $subPartner = new Partner(['type' => 'sub', 'status' => 'inactive']);
        $this->assertFalse($subPartner->isMain());
        $this->assertTrue($subPartner->isSub());
        $this->assertFalse($subPartner->isActive());

        $query = (new Partner)->newQuery();
        $scoped = $mainPartner->scopeActive($query);
        $this->assertStringContainsString('status', $scoped->toSql());
    }

    public function test_partner_policy_view_any(): void
    {
        $admin = new User(['role' => Role::ADMIN->value, 'status' => 'active']);
        $mainUser = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $subUser = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $partnerUser = new User(['role' => Role::PARTNER_USER->value, 'status' => 'active']);

        $this->assertTrue($this->policy->before($admin, 'viewAny'));
        $this->assertTrue($this->policy->viewAny($mainUser));
        $this->assertTrue($this->policy->viewAny($subUser));
        $this->assertFalse($this->policy->viewAny($partnerUser));
    }

    public function test_partner_policy_deactivate_is_blocked_for_non_admin(): void
    {
        $admin = new User(['role' => Role::ADMIN->value, 'status' => 'active']);
        $mainUser = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $subUser = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);

        $partner = new Partner(['id' => 1, 'type' => 'main']);

        $this->assertTrue($this->policy->before($admin, 'deactivate'));
        $this->assertFalse($this->policy->deactivate($mainUser, $partner));
        $this->assertFalse($this->policy->deactivate($subUser, $partner));
    }

    public function test_partner_deletion_is_strictly_denied_in_policy(): void
    {
        $mainUser = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $partner = new Partner(['id' => 1]);

        $this->assertFalse($this->policy->delete($mainUser, $partner));
    }

    public function test_create_partner_action_is_safely_blocked_pending_confirmation(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $action = new CreatePartnerAction($logger);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NEEDS BUSINESS CONFIRMATION #1');

        $action->execute(['name' => 'Acme Partner']);
    }

    public function test_deactivate_partner_action_is_safely_blocked_pending_confirmation(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $action = new DeactivatePartnerAction($logger);

        $partner = new Partner(['id' => 1, 'name' => 'Acme Partner', 'status' => 'active']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NEEDS BUSINESS CONFIRMATION #5');

        $action->execute($partner);
    }

    public function test_update_partner_action_updates_name_and_logs_audit(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                'partner.updated',
                $this->isInstanceOf(User::class),
                $this->isInstanceOf(Partner::class),
                ['name' => 'Old Name'],
                ['name' => 'New Name']
            );

        $action = new UpdatePartnerAction($logger);

        $partner = new class(['name' => 'Old Name']) extends Partner
        {
            public function update(array $attributes = [], array $options = [])
            {
                $this->fill($attributes);

                return true;
            }
        };

        $actor = new User(['id' => 1, 'role' => Role::ADMIN->value]);

        $result = $action->execute($partner, ['name' => 'New Name'], $actor);

        $this->assertEquals('New Name', $result->name);
    }
}
