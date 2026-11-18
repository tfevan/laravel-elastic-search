<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HighlightRequest;
use App\Services\Search\HighlightService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HighlightController extends Controller
{
    /**
     * Search products and return highlighted snippets.
     */
    public function __invoke(HighlightRequest $request, HighlightService $service): JsonResponse
    {
        try {
            $data = $service->search($request->string('q')->toString(), $request->boolean('fuzzy'));
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return response()->json(['data' => $data]);
    }
}
