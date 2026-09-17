<?php

namespace App\Domain\Products\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Products\Product;

class CreateProductAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Create a new product.
     *
     * @param  array{code: string, name: string, description?: string|null, is_active?: bool}  $data
     */
    public function execute(array $data, ?User $actor = null): Product
    {
        $product = Product::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->auditLogger->log(
            'product.created',
            $actor,
            $product,
            null,
            $product->only(['code', 'name', 'is_active'])
        );

        return $product;
    }
}
