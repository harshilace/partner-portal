<?php

namespace App\Http\Controllers\Products;

use App\Domain\Products\Actions\CreateProductAction;
use App\Domain\Products\Actions\UpdateProductAction;
use App\Domain\Products\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse|Response
    {
        Gate::authorize('viewAny', Product::class);

        $products = Product::with('plans')->get();

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'data' => $products,
            ]);
        }

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        Gate::authorize('view', $product);

        $product->load('plans');

        return response()->json([
            'data' => $product,
        ]);
    }

    public function store(StoreProductRequest $request, CreateProductAction $action): JsonResponse
    {
        Gate::authorize('create', Product::class);

        $product = $action->execute($request->validated(), $request->user());

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }

    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $action): JsonResponse
    {
        Gate::authorize('update', $product);

        $updated = $action->execute($product, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $updated,
        ]);
    }
}
