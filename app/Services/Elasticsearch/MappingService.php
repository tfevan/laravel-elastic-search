<?php

namespace App\Services\Elasticsearch;

use App\Exceptions\IndexNotFoundException;
use App\Models\Product;
use App\Services\Elasticsearch\Concerns\ConvertsResponses;
use Elastic\Client\ClientBuilderInterface;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Symfony\Component\HttpFoundation\Response;

class MappingService
{
    use ConvertsResponses;

    /**
     * Create a new service instance.
     */
    public function __construct(private readonly ClientBuilderInterface $clientBuilder) {}

    /**
     * Describe the mapping and the analysis settings of the products index.
     */
    public function describe(): array
    {
        $client = $this->clientBuilder->default();

        try {
            $mapping = $this->toArray($client->indices()->getMapping(['index' => $this->indexName()]));
            $settings = $this->toArray($client->indices()->getSettings(['index' => $this->indexName()]));
        } catch (ClientResponseException $exception) {
            $this->fail($exception);
        }

        $indexSettings = array_values($settings)[0]['settings']['index'] ?? [];

        return [
            'mapping' => array_values($mapping)[0]['mappings'] ?? [],
            'settings' => [
                'shards' => (int) ($indexSettings['number_of_shards'] ?? 0),
                'replicas' => (int) ($indexSettings['number_of_replicas'] ?? 0),
                'analyzers' => array_keys($indexSettings['analysis']['analyzer'] ?? []),
                'filters' => array_keys($indexSettings['analysis']['filter'] ?? []),
            ],
        ];
    }

    /**
     * Break a text into tokens with the given analyzer.
     */
    public function analyze(string $text, string $analyzer): array
    {
        try {
            $response = $this->clientBuilder->default()->indices()->analyze([
                'index' => $this->indexName(),
                'body' => ['analyzer' => $analyzer, 'text' => $text],
            ]);
        } catch (ClientResponseException $exception) {
            $this->fail($exception);
        }

        return array_map(
            fn (array $token): array => [
                'token' => $token['token'],
                'position' => $token['position'],
                'type' => $token['type'],
            ],
            $this->toArray($response)['tokens'],
        );
    }

    /**
     * Get the name of the products index.
     */
    private function indexName(): string
    {
        return (new Product)->searchableAs();
    }

    /**
     * Translate a missing index into a domain exception and rethrow anything else.
     */
    private function fail(ClientResponseException $exception): never
    {
        if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
            throw new IndexNotFoundException('The products index does not exist. Run php artisan elastic:migrate.', 0, $exception);
        }

        throw $exception;
    }
}
