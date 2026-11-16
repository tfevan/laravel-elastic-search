<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    /**
     * List products, newest first.
     */
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(Product::query()->latest('id')->paginate(10));
    }

    /**
     * Create a product; Scout indexes it automatically.
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::query()->create($request->validated());

        return (new ProductResource($product))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update a product; Scout re-indexes it automatically.
     */
    public function update(ProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());

        return new ProductResource($product);
    }

    /**
     * Delete a product; Scout removes it from the index automatically.
     */
    public function destroy(Product $product): Response
    {
        $product->delete();

        return response()->noContent();
    }
}
