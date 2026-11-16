<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivationRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductActivationController extends Controller
{
    /**
     * Activate or deactivate a product; Scout adds or removes it from the index.
     */
    public function __invoke(ActivationRequest $request, Product $product): JsonResponse
    {
        $product->update(['is_active' => $request->boolean('is_active')]);

        return response()->json(['data' => ['is_active' => $product->is_active]]);
    }
}
