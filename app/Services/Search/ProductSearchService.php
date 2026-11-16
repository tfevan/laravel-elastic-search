<?php

namespace App\Services\Search;

use App\Exceptions\InvalidSearchQueryException;
use App\Models\Product;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Symfony\Component\HttpFoundation\Response;

class ProductSearchService
{
    /**
     * Search products with the Elasticsearch query string syntax.
     */
    public function search(string $query, int $limit = 20): array
    {
        try {
            return Product::search($query)->take($limit)->get()->all();
        } catch (ClientResponseException $exception) {
            if ($exception->getCode() === Response::HTTP_BAD_REQUEST) {
                throw new InvalidSearchQueryException('Invalid search syntax.', 0, $exception);
            }

            throw $exception;
        }
    }
}
