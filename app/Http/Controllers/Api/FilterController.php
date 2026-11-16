<?php

namespace App\Http\Controllers\Api;

use App\Data\ProductFilters;
use App\Exceptions\InvalidSearchQueryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Resources\ProductResource;
use App\Services\Search\ProductFilterService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class FilterController extends Controller
{
    /**
     * Return products matching the given filters, in the requested order.
     */
    public function __invoke(FilterRequest $request, ProductFilterService $service): AnonymousResourceCollection|JsonResponse
    {
        try {
            $products = $service->filter(ProductFilters::fromArray($request->validated()));
        } catch (InvalidSearchQueryException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return ProductResource::collection($products);
    }
}
