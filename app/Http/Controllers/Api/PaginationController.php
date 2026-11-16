<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidSearchQueryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationRequest;
use App\Http\Resources\ProductResource;
use App\Services\Search\ProductPaginationService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class PaginationController extends Controller
{
    /**
     * Return one page of search results with pagination metadata.
     */
    public function __invoke(PaginationRequest $request, ProductPaginationService $service): AnonymousResourceCollection|JsonResponse
    {
        try {
            $page = $service->paginate($request->string('q')->toString(), $request->integer('per_page', 10));
        } catch (InvalidSearchQueryException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NoNodeAvailableException $exception) {
            report($exception);

            return response()->json(['message' => 'Elasticsearch is unreachable.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $paginator = new LengthAwarePaginator($page->items, $page->total, $page->perPage, $page->page, [
            'path' => $request->url(),
        ]);

        return ProductResource::collection($paginator);
    }
}
