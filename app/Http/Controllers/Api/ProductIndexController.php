<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Elasticsearch\ProductIndexService;
use Illuminate\Http\JsonResponse;

class ProductIndexController extends Controller
{
    /**
     * Compare the database row count with the Elasticsearch document count.
     */
    public function summary(ProductIndexService $service): JsonResponse
    {
        return response()->json([
            'data' => [
                'database' => Product::query()->where('is_active', true)->count(),
                'elasticsearch' => $service->count(),
            ],
        ]);
    }

    /**
     * Show the document Elasticsearch stores for one product.
     */
    public function document(Product $product, ProductIndexService $service): JsonResponse
    {
        $document = $service->document($product);

        abort_if($document === null, 404, 'This product is not indexed.');

        return response()->json(['data' => $document]);
    }
}
