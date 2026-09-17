<?php

namespace Tests\Feature\Partners;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Actions\CreateSubPartnerAction;
use App\Domain\Partners\Actions\UpdateSubPartnerAction;
use App\Domain\Partners\Partner;
use App\Policies\PartnerPolicy;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class SubPartnerManagementTest extends TestCase
{
    protected PartnerPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PartnerPolicy;
    }

    public function test_admin_and_main_partner_can_create_sub_partner_under_own_partner(): void
    {
        $admin = new User(['role' => Role::ADMIN->value, 'status' => 'active']);
        $this->assertTrue($this->policy->before($admin, 'createSubPartner'));

        $mainPartner = new Partner(['id' => 10, 'type' => 'main']);
        $mainPartner->id = 10;

        $mainUser = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $mainUser->setRelation('partners', collect([$mainPartner]));

        $this->assertTrue($this->policy->createSubPartner($mainUser, $mainPartner));
    }

    public function test_main_partner_cannot_create_sub_partner_under_foreign_partner(): void
    {
        $ownMainPartner = new Partner(['id' => 10, 'type' => 'main']);
        $ownMainPartner->id = 10;

        $foreignMainPartner = new Partner(['id' => 99, 'type' => 'main']);
        $foreignMainPartner->id = 99;

        $mainUser = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $mainUser->setRelation('partners', collect([$ownMainPartner]));

        $this->assertFalse($this->policy->createSubPartner($mainUser, $foreignMainPartner));
    }

    public function test_sub_partner_cannot_create_sub_partners(): void
    {
        $subPartner = new Partner(['id' => 20, 'parent_partner_id' => 10, 'type' => 'sub']);
        $subPartner->id = 20;

        $subUser = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $subUser->setRelation('partners', collect([$subPartner]));

        $this->assertFalse($this->policy->createSubPartner($subUser, $subPartner));
    }

    public function test_create_sub_partner_action_is_safely_blocked_pending_confirmation(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $action = new CreateSubPartnerAction($logger);

        $parent = new Partner(['id' => 10, 'type' => 'main']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NEEDS BUSINESS CONFIRMATION #1');

        $action->execute(['name' => 'New Sub Partner'], $parent);
    }

    public function test_update_sub_partner_action_updates_name_and_logs_audit(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                'partner.updated',
                $this->isInstanceOf(User::class),
                $this->isInstanceOf(Partner::class),
                ['name' => 'Old Sub Name'],
                ['name' => 'New Sub Name']
            );

        $action = new UpdateSubPartnerAction($logger);

        $subPartner = new class(['name' => 'Old Sub Name']) extends Partner
        {
            public function update(array $attributes = [], array $options = [])
            {
                $this->fill($attributes);

                return true;
            }
        };

        $actor = new User(['id' => 5, 'role' => Role::MAIN_PARTNER->value]);

        $result = $action->execute($subPartner, ['name' => 'New Sub Name'], $actor);

        $this->assertEquals('New Sub Name', $result->name);
    }

    public function test_main_partner_update_scope_allows_own_sub_partner_and_denies_foreign(): void
    {
        $ownMain = new Partner(['id' => 10, 'type' => 'main']);
        $ownMain->id = 10;

        $ownSub = new Partner(['id' => 20, 'parent_partner_id' => 10, 'type' => 'sub']);
        $ownSub->id = 20;

        $foreignSub = new Partner(['id' => 30, 'parent_partner_id' => 99, 'type' => 'sub']);
        $foreignSub->id = 30;

        $mainUser = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $mainUser->setRelation('partners', collect([$ownMain]));

        $this->assertTrue($this->policy->update($mainUser, $ownSub));
        $this->assertFalse($this->policy->update($mainUser, $foreignSub));
    }
}
