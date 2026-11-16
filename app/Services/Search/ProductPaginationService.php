<?php

namespace App\Services\Search;

use App\Data\ProductPage;
use App\Exceptions\InvalidSearchQueryException;
use App\Models\Product;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Symfony\Component\HttpFoundation\Response;

class ProductPaginationService
{
    /**
     * Search products and return one page of results with the total count.
     */
    public function paginate(string $query, int $perPage): ProductPage
    {
        try {
            $paginator = Product::search($query)->paginate($perPage);
        } catch (ClientResponseException $exception) {
            if ($exception->getCode() === Response::HTTP_BAD_REQUEST) {
                throw new InvalidSearchQueryException('Invalid search syntax.', 0, $exception);
            }

            throw $exception;
        }

        return new ProductPage(
            $paginator->items(),
            $paginator->total(),
            $paginator->currentPage(),
            $paginator->perPage(),
        );
    }
}
