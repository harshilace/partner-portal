<?php

namespace Tests\Feature\Products;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Products\Product;
use App\Domain\Products\ProductPlan;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProductPolicyTest extends TestCase
{
    public function test_admin_can_manage_products_and_plans_but_delete_is_blocked(): void
    {
        $admin = new User(['role' => Role::ADMIN->value, 'status' => 'active']);
        $admin->id = 1;

        $product = new Product(['code' => 'PROD-1', 'name' => 'CRM']);
        $product->id = 10;
        $plan = new ProductPlan(['product_id' => 10, 'code' => 'P1', 'name' => 'Plan 1', 'price' => '10.00']);
        $plan->id = 20;

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Product::class));
        $this->assertTrue(Gate::forUser($admin)->allows('view', $product));
        $this->assertTrue(Gate::forUser($admin)->allows('create', Product::class));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $product));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $product));

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', ProductPlan::class));
        $this->assertTrue(Gate::forUser($admin)->allows('view', $plan));
        $this->assertTrue(Gate::forUser($admin)->allows('create', ProductPlan::class));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $plan));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $plan));
    }

    public function test_partners_can_view_products_and_plans_but_cannot_modify_or_delete(): void
    {
        $partnerUser = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $partnerUser->id = 2;

        $product = new Product(['code' => 'PROD-1', 'name' => 'CRM']);
        $product->id = 10;
        $plan = new ProductPlan(['product_id' => 10, 'code' => 'P1', 'name' => 'Plan 1', 'price' => '10.00']);
        $plan->id = 20;

        $this->assertTrue(Gate::forUser($partnerUser)->allows('viewAny', Product::class));
        $this->assertTrue(Gate::forUser($partnerUser)->allows('view', $product));
        $this->assertFalse(Gate::forUser($partnerUser)->allows('create', Product::class));
        $this->assertFalse(Gate::forUser($partnerUser)->allows('update', $product));
        $this->assertFalse(Gate::forUser($partnerUser)->allows('delete', $product));

        $this->assertTrue(Gate::forUser($partnerUser)->allows('viewAny', ProductPlan::class));
        $this->assertTrue(Gate::forUser($partnerUser)->allows('view', $plan));
        $this->assertFalse(Gate::forUser($partnerUser)->allows('create', ProductPlan::class));
        $this->assertFalse(Gate::forUser($partnerUser)->allows('update', $plan));
        $this->assertFalse(Gate::forUser($partnerUser)->allows('delete', $plan));
    }
}
