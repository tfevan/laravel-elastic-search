<?php

namespace App\Services\Elasticsearch;

use App\Models\Product;
use Elastic\Client\ClientBuilderInterface;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class ProductIndexService
{
    /**
     * Create a new service instance.
     */
    public function __construct(private readonly ClientBuilderInterface $clientBuilder) {}

    /**
     * Count the documents stored in the products index.
     */
    public function count(): int
    {
        try {
            $response = $this->clientBuilder->default()->count(['index' => (new Product)->searchableAs()]);
        } catch (ClientResponseException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return 0;
            }

            throw $exception;
        }

        return (int) $this->toArray($response)['count'];
    }

    /**
     * Fetch the indexed document of a product, or null when it is not indexed.
     */
    public function document(Product $product): ?array
    {
        try {
            $response = $this->clientBuilder->default()->get([
                'index' => $product->searchableAs(),
                'id' => $product->getScoutKey(),
            ]);
        } catch (ClientResponseException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return null;
            }

            throw $exception;
        }

        return $this->toArray($response)['_source'];
    }

    /**
     * Convert a synchronous client response to an array.
     */
    private function toArray(mixed $response): array
    {
        if (! $response instanceof Elasticsearch) {
            throw new UnexpectedValueException('Expected a synchronous Elasticsearch response.');
        }

        return $response->asArray();
    }
}
