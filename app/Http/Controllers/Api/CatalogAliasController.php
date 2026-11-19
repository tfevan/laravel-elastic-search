<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AliasOperationException;
use App\Http\Controllers\Controller;
use App\Services\Elasticsearch\AliasReindexService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CatalogAliasController extends Controller
{
    /**
     * Show the alias and its versions.
     */
    public function status(AliasReindexService $service): JsonResponse
    {
        return $this->respond(fn (): array => $service->status());
    }

    /**
     * Create the next version and switch the alias to it.
     */
    public function reindex(AliasReindexService $service): JsonResponse
    {
        return $this->respond(fn (): array => $service->reindex());
    }

    /**
     * Switch the alias back to the previous version.
     */
    public function rollback(AliasReindexService $service): JsonResponse
    {
        return $this->respond(fn (): array => $service->rollback());
    }

    /**
     * Delete the demo indices.
     */
    public function reset(AliasReindexService $service): JsonResponse
    {
        return $this->respond(fn (): array => $service->reset());
    }

    /**
     * Run an alias operation and translate its failures into JSON responses.
     */
    private function respond(callable $operation): JsonResponse
    {
        try {
            return response()->json(['data' => $operation()]);
        } catch (AliasOperationException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_CONFLICT);
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
