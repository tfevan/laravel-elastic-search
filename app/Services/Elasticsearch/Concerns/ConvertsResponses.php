<?php

namespace App\Services\Elasticsearch\Concerns;

use Elastic\Elasticsearch\Response\Elasticsearch;
use UnexpectedValueException;

trait ConvertsResponses
{
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
