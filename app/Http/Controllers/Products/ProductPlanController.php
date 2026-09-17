<?php

namespace App\Http\Controllers\Products;

use App\Domain\Products\Actions\CreateProductPlanAction;
use App\Domain\Products\Actions\UpdateProductPlanAction;
use App\Domain\Products\Product;
use App\Domain\Products\ProductPlan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Products\StoreProductPlanRequest;
use App\Http\Requests\Products\UpdateProductPlanRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProductPlanController extends Controller
{
    public function index(Product $product): JsonResponse
    {
        Gate::authorize('viewAny', ProductPlan::class);

        return response()->json([
            'data' => $product->plans()->get(),
        ]);
    }

    public function show(Product $product, ProductPlan $plan): JsonResponse
    {
        Gate::authorize('view', $plan);

        return response()->json([
            'data' => $plan,
        ]);
    }

    public function store(StoreProductPlanRequest $request, Product $product, CreateProductPlanAction $action): JsonResponse
    {
        Gate::authorize('create', ProductPlan::class);

        $plan = $action->execute($product, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Product plan created successfully.',
            'data' => $plan,
        ], 201);
    }

    public function update(UpdateProductPlanRequest $request, Product $product, ProductPlan $plan, UpdateProductPlanAction $action): JsonResponse
    {
        Gate::authorize('update', $plan);

        $updated = $action->execute($plan, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Product plan updated successfully.',
            'data' => $updated,
        ]);
    }
}
