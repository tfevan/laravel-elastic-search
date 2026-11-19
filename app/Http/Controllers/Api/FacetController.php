<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FacetRequest;
use App\Services\Search\FacetService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FacetController extends Controller
{
    /**
     * Search products and return hits together with facet counts.
     */
    public function __invoke(FacetRequest $request, FacetService $service): JsonResponse
    {
        try {
            return response()->json(['data' => $service->search($request->selection())]);
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
