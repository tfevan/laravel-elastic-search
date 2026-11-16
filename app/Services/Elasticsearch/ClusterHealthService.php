<?php

namespace App\Services\Elasticsearch;

use Elastic\Client\ClientBuilderInterface;
use Elastic\Elasticsearch\Response\Elasticsearch;
use UnexpectedValueException;

class ClusterHealthService
{
    /**
     * Create a new service instance.
     */
    public function __construct(private readonly ClientBuilderInterface $clientBuilder) {}

    /**
     * Collect cluster status, version and index statistics.
     */
    public function report(): array
    {
        $client = $this->clientBuilder->default();

        $info = $this->toArray($client->info());
        $health = $this->toArray($client->cluster()->health());
        $indices = $this->toArray($client->cat()->indices(['format' => 'json']));

        return [
            'cluster' => [
                'name' => $health['cluster_name'],
                'status' => $health['status'],
                'nodes' => $health['number_of_nodes'],
            ],
            'version' => $info['version']['number'],
            'indices' => $this->formatIndices($indices),
        ];
    }

    /**
     * Keep user indices only and map them to a compact shape.
     */
    private function formatIndices(array $indices): array
    {
        return collect($indices)
            ->reject(fn (array $index): bool => str_starts_with($index['index'], '.'))
            ->map(fn (array $index): array => [
                'name' => $index['index'],
                'health' => $index['health'],
                'status' => $index['status'],
                'documents' => (int) $index['docs.count'],
                'size' => $index['store.size'],
            ])
            ->sortBy('name')
            ->values()
            ->all();
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
