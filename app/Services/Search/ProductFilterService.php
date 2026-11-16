<?php

namespace App\Services\Search;

use App\Data\ProductFilters;
use App\Exceptions\InvalidSearchQueryException;
use App\Models\Product;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Symfony\Component\HttpFoundation\Response;

class ProductFilterService
{
    private const SORTS = [
        'price_asc' => ['price', 'asc'],
        'price_desc' => ['price', 'desc'],
        'rating_desc' => ['rating', 'desc'],
        'name_asc' => ['name.keyword', 'asc'],
    ];

    /**
     * Search products with filters and sorting applied.
     */
    public function filter(ProductFilters $filters, int $limit = 20): array
    {
        $builder = Product::search($this->buildQuery($filters));

        if ($filters->category !== null) {
            $builder->where('category', $filters->category);
        }

        if ($filters->brands !== []) {
            $builder->whereIn('brand', $filters->brands);
        }

        $order = self::SORTS[$filters->sort] ?? null;

        if ($order !== null) {
            $builder->orderBy($order[0], $order[1]);
        }

        try {
            return $builder->take($limit)->get()->all();
        } catch (ClientResponseException $exception) {
            if ($exception->getCode() === Response::HTTP_BAD_REQUEST) {
                throw new InvalidSearchQueryException('Invalid search syntax.', 0, $exception);
            }

            throw $exception;
        }
    }

    /**
     * Combine the free text and the price range into one query string.
     */
    private function buildQuery(ProductFilters $filters): string
    {
        $clauses = [];

        if ($filters->query !== '') {
            $clauses[] = '('.$filters->query.')';
        }

        if ($filters->minPrice !== null || $filters->maxPrice !== null) {
            $clauses[] = sprintf('price:[%s TO %s]', $filters->minPrice ?? '*', $filters->maxPrice ?? '*');
        }

        return implode(' AND ', $clauses);
    }
}
