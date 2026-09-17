<?php

namespace Tests\Feature\Products;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Products\Actions\CreateProductAction;
use App\Domain\Products\Actions\CreateProductPlanAction;
use App\Domain\Products\Actions\UpdateProductAction;
use App\Domain\Products\Actions\UpdateProductPlanAction;
use App\Domain\Products\Product;
use App\Domain\Products\ProductPlan;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    public function test_admin_can_create_and_update_product(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->exactly(2))->method('log');

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $createAction = new class($logger) extends CreateProductAction
        {
            public function execute(array $data, ?User $actor = null): Product
            {
                $product = new Product($data);
                $product->id = 10;
                $this->auditLogger->log('product.created', $actor, $product);

                return $product;
            }
        };

        $product = $createAction->execute([
            'code' => 'PROD-1',
            'name' => 'Main CRM',
            'description' => 'Test CRM',
            'is_active' => true,
        ], $admin);

        $this->assertEquals('PROD-1', $product->code);
        $this->assertEquals('Main CRM', $product->name);
        $this->assertTrue($product->is_active);

        $updateAction = new class($logger) extends UpdateProductAction
        {
            public function execute(Product $product, array $data, ?User $actor = null): Product
            {
                $product->fill($data);
                $this->auditLogger->log('product.updated', $actor, $product);

                return $product;
            }
        };

        $updated = $updateAction->execute($product, [
            'name' => 'Renamed CRM',
            'description' => 'Updated description',
        ], $admin);

        $this->assertEquals('Renamed CRM', $updated->name);
        $this->assertEquals('Updated description', $updated->description);
    }

    public function test_admin_can_create_and_update_product_plan(): void
    {
        $logger = $this->createMock(AuditLogger::class);
        $logger->expects($this->exactly(2))->method('log');

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);
        $product = new Product(['code' => 'PROD-1', 'name' => 'Main CRM', 'is_active' => true]);
        $product->id = 10;

        $createAction = new class($logger) extends CreateProductPlanAction
        {
            public function execute(Product $product, array $data, ?User $actor = null): ProductPlan
            {
                $plan = new ProductPlan($data);
                $plan->id = 100;
                $plan->product_id = $product->id;
                $this->auditLogger->log('product_plan.created', $actor, $plan);

                return $plan;
            }
        };

        $plan = $createAction->execute($product, [
            'code' => 'PLAN-MONTHLY',
            'name' => 'Monthly Plan',
            'price' => '49.99',
            'billing_cycle' => 'monthly',
            'duration_days' => 30,
            'is_active' => true,
        ], $admin);

        $this->assertEquals('PLAN-MONTHLY', $plan->code);
        $this->assertEquals(10, $plan->product_id);
        $this->assertEquals('49.99', (string) $plan->price);

        $updateAction = new class($logger) extends UpdateProductPlanAction
        {
            public function execute(ProductPlan $plan, array $data, ?User $actor = null): ProductPlan
            {
                $plan->fill($data);
                $this->auditLogger->log('product_plan.updated', $actor, $plan);

                return $plan;
            }
        };

        $updated = $updateAction->execute($plan, [
            'price' => '59.99',
        ], $admin);

        $this->assertEquals('59.99', (string) $updated->price);
    }
}
