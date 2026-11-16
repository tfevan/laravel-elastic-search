<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\IndexNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyzeRequest;
use App\Services\Elasticsearch\MappingService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MappingController extends Controller
{
    /**
     * Show the mapping and analysis settings of the products index.
     */
    public function show(MappingService $service): JsonResponse
    {
        try {
            return response()->json(['data' => $service->describe()]);
        } catch (IndexNotFoundException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * Show how an analyzer breaks a text into tokens.
     */
    public function analyze(AnalyzeRequest $request, MappingService $service): JsonResponse
    {
        try {
            $tokens = $service->analyze($request->string('text')->toString(), $request->string('analyzer')->toString());
        } catch (IndexNotFoundException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return response()->json(['data' => ['analyzer' => $request->string('analyzer')->toString(), 'tokens' => $tokens]]);
    }
}
