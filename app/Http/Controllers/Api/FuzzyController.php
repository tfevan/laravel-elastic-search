<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FuzzyRequest;
use App\Services\Search\FuzzyService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FuzzyController extends Controller
{
    /**
     * Run a typo tolerant search and return hits, match counts and a spelling suggestion.
     */
    public function __invoke(FuzzyRequest $request, FuzzyService $service): JsonResponse
    {
        try {
            return response()->json(['data' => $service->search($request->options())]);
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
