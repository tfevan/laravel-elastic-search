<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidSearchQueryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Models\Product;
use App\Services\Elasticsearch\ProductIndexService;
use App\Services\Search\RawSearchService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    /**
     * Show what a product sends to the index and what the index actually stores.
     */
    public function show(Product $product, ProductIndexService $index): JsonResponse
    {
        try {
            $indexed = $index->document($product);
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return response()->json([
            'data' => [
                'index' => $product->searchableAs(),
                'scout_key' => $product->getScoutKey(),
                'is_active' => $product->is_active,
                'should_be_searchable' => $product->shouldBeSearchable(),
                'searchable_array' => $product->toSearchableArray(),
                'indexed_document' => $indexed,
            ],
        ]);
    }

    /**
     * Return the raw Elasticsearch hits for a query.
     */
    public function raw(SearchRequest $request, RawSearchService $service): JsonResponse
    {
        try {
            $result = $service->search($request->string('q')->toString());
        } catch (InvalidSearchQueryException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return response()->json(['data' => $result]);
    }
}
