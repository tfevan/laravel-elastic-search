<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeoRequest;
use App\Services\Search\GeoService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GeoController extends Controller
{
    /**
     * Return the stores nearest to a point, closest first.
     */
    public function __invoke(GeoRequest $request, GeoService $service): JsonResponse
    {
        try {
            return response()->json(['data' => $service->search($request->geoQuery())]);
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
