<?php

namespace App\Domain\Products\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Products\ProductPlan;

class UpdateProductPlanAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Update an existing product plan.
     *
     * @param  array{code?: string, name?: string, price?: string|float, billing_cycle?: string|null, duration_days?: int|null, is_active?: bool}  $data
     */
    public function execute(ProductPlan $plan, array $data, ?User $actor = null): ProductPlan
    {
        $oldValues = $plan->only(['code', 'name', 'price', 'billing_cycle', 'duration_days', 'is_active']);

        $plan->update($data);

        $newValues = $plan->only(['code', 'name', 'price', 'billing_cycle', 'duration_days', 'is_active']);

        $this->auditLogger->log(
            'product_plan.updated',
            $actor,
            $plan,
            $oldValues,
            $newValues
        );

        return $plan;
    }
}
