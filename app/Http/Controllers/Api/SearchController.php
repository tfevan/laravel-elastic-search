<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidSearchQueryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\ProductResource;
use App\Services\Search\ProductSearchService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
    /**
     * Search products and return the matching results.
     */
    public function __invoke(SearchRequest $request, ProductSearchService $service): AnonymousResourceCollection|JsonResponse
    {
        try {
            $products = $service->search($request->string('q')->toString());
        } catch (InvalidSearchQueryException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return ProductResource::collection($products);
    }
}
