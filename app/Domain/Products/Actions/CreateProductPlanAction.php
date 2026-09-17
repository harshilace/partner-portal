<?php

namespace App\Domain\Products\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Products\Product;
use App\Domain\Products\ProductPlan;

class CreateProductPlanAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Create a new product plan.
     *
     * @param  array{product_id: int, code: string, name: string, price: string|float, billing_cycle?: string|null, duration_days?: int|null, is_active?: bool}  $data
     */
    public function execute(Product $product, array $data, ?User $actor = null): ProductPlan
    {
        $plan = ProductPlan::create([
            'product_id' => $product->id,
            'code' => $data['code'],
            'name' => $data['name'],
            'price' => $data['price'],
            'billing_cycle' => $data['billing_cycle'] ?? null,
            'duration_days' => $data['duration_days'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->auditLogger->log(
            'product_plan.created',
            $actor,
            $plan,
            null,
            $plan->only(['product_id', 'code', 'name', 'price', 'is_active'])
        );

        return $plan;
    }
}
