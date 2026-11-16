<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RelevanceRequest;
use App\Models\Product;
use App\Services\Search\RelevanceService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RelevanceController extends Controller
{
    /**
     * Search with a configurable bool query and return scored hits.
     */
    public function search(RelevanceRequest $request, RelevanceService $service): JsonResponse
    {
        try {
            return response()->json(['data' => $service->search($request->options())]);
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * Explain how one product was scored by the query.
     */
    public function explain(Product $product, RelevanceRequest $request, RelevanceService $service): JsonResponse
    {
        try {
            $explanation = $service->explain($product, $request->options());
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if ($explanation === null) {
            return response()->json(['message' => 'This product is not indexed.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $explanation]);
    }
}
