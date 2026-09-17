<?php

namespace App\Domain\Products\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Products\Product;

class UpdateProductAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Update an existing product.
     *
     * @param  array{code?: string, name?: string, description?: string|null, is_active?: bool}  $data
     */
    public function execute(Product $product, array $data, ?User $actor = null): Product
    {
        $oldValues = $product->only(['code', 'name', 'description', 'is_active']);

        $product->update($data);

        $newValues = $product->only(['code', 'name', 'description', 'is_active']);

        $this->auditLogger->log(
            'product.updated',
            $actor,
            $product,
            $oldValues,
            $newValues
        );

        return $product;
    }
}
