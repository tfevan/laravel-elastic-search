<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidCursorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeepPaginationRequest;
use App\Services\Search\DeepPaginationService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DeepPaginationController extends Controller
{
    /**
     * Return one cursor based page of products.
     */
    public function __invoke(DeepPaginationRequest $request, DeepPaginationService $service): JsonResponse
    {
        try {
            $page = $service->page(
                $request->string('sort', 'price_asc')->toString(),
                $request->integer('size', 20),
                $request->filled('cursor') ? $request->string('cursor')->toString() : null,
            );
        } catch (InvalidCursorException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return response()->json(['data' => $page]);
    }
}
