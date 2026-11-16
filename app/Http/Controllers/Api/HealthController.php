<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Elasticsearch\ClusterHealthService;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HealthController extends Controller
{
    /**
     * Return the Elasticsearch cluster health report.
     */
    public function __invoke(ClusterHealthService $service): JsonResponse
    {
        try {
            return response()->json(['data' => $service->report()]);
        } catch (NoNodeAvailableException|ElasticsearchException $exception) {
            report($exception);

            return response()->json(
                ['message' => 'Elasticsearch is unreachable.'],
                Response::HTTP_SERVICE_UNAVAILABLE,
            );
        }
    }
}
