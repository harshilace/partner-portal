<?php

namespace Tests\Feature\Sales;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use App\Domain\Payments\Order;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class OrderPolicyAndScopingTest extends TestCase
{
    public function test_admin_can_view_any_order(): void
    {
        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);
        $order = new Order(['id' => 10, 'partner_id' => 2, 'sub_partner_id' => 20]);

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Order::class));
        $this->assertTrue(Gate::forUser($admin)->allows('view', $order));
        $this->assertFalse(Gate::forUser($admin)->allows('update', $order));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $order));
    }

    public function test_sub_partner_can_only_view_own_orders(): void
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

        $ownOrder = new Order(['partner_id' => 2, 'sub_partner_id' => 20]);
        $otherOrder = new Order(['partner_id' => 2, 'sub_partner_id' => 21]);
        $directOrder = new Order(['partner_id' => 2, 'sub_partner_id' => null]);

        $this->assertTrue(Gate::forUser($subUser)->allows('view', $ownOrder));
        $this->assertFalse(Gate::forUser($subUser)->allows('view', $otherOrder));
        $this->assertFalse(Gate::forUser($subUser)->allows('view', $directOrder));
    }

    public function test_main_partner_can_view_direct_orders_but_sub_partner_rows_are_blocked(): void
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

        $directOrder = new Order(['partner_id' => 2, 'sub_partner_id' => null]);
        $subPartnerOrder = new Order(['partner_id' => 2, 'sub_partner_id' => 20]);
        $otherMainOrder = new Order(['partner_id' => 3, 'sub_partner_id' => null]);

        $this->assertTrue(Gate::forUser($mainUser)->allows('view', $directOrder));
        // Sub-partner row access remains BLOCKED as unresolved business confirmation
        $this->assertFalse(Gate::forUser($mainUser)->allows('view', $subPartnerOrder));
        $this->assertFalse(Gate::forUser($mainUser)->allows('view', $otherMainOrder));
    }
}
