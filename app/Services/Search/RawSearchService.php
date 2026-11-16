<?php

namespace App\Services\Search;

use App\Exceptions\InvalidSearchQueryException;
use App\Models\Product;
use Elastic\Adapter\Search\Hit;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Symfony\Component\HttpFoundation\Response;

class RawSearchService
{
    /**
     * Run a search and return the raw hits with their relevance scores.
     */
    public function search(string $query, int $limit = 5): array
    {
        try {
            $result = Product::search($query)->take($limit)->raw();
        } catch (ClientResponseException $exception) {
            if ($exception->getCode() === Response::HTTP_BAD_REQUEST) {
                throw new InvalidSearchQueryException('Invalid search syntax.', 0, $exception);
            }

            throw $exception;
        }

        return [
            'total' => $result->total(),
            'hits' => $result->hits()->map(fn (Hit $hit): array => [
                'id' => $hit->document()->id(),
                'score' => $hit->score(),
                'source' => $hit->document()->content(),
            ])->values()->all(),
        ];
    }
}
