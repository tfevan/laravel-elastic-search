<?php

namespace App\Services\Search;

use App\Exceptions\InvalidCursorException;
use App\Models\Product;
use App\Services\Elasticsearch\Concerns\ConvertsResponses;
use Elastic\Client\ClientBuilderInterface;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

class DeepPaginationService
{
    use ConvertsResponses;

    public const SORTS = [
        'price_asc' => ['price' => 'asc'],
        'price_desc' => ['price' => 'desc'],
        'rating_desc' => ['rating' => 'desc'],
        'name_asc' => ['name.keyword' => 'asc'],
    ];

    private const KEEP_ALIVE = '2m';

    /**
     * Create a new service instance.
     */
    public function __construct(private readonly ClientBuilderInterface $clientBuilder) {}

    /**
     * Fetch one page; without a cursor the first page is returned and a new point in time is opened.
     */
    public function page(string $sort, int $size, ?string $cursor): array
    {
        $client = $this->clientBuilder->default();

        if ($cursor === null) {
            $pit = (string) $this->toArray($client->openPointInTime([
                'index' => (new Product)->searchableAs(),
                'keep_alive' => self::KEEP_ALIVE,
            ]))['id'];
            $after = null;
        } else {
            ['pit' => $pit, 'after' => $after, 'sort' => $sort] = $this->decode($cursor);
        }

        $body = [
            'size' => $size,
            'track_total_hits' => false,
            '_source' => ['name', 'category', 'brand', 'price', 'rating'],
            'query' => ['match_all' => new stdClass],
            'sort' => [self::SORTS[$sort]],
            'pit' => ['id' => $pit, 'keep_alive' => self::KEEP_ALIVE],
        ];

        if ($after !== null) {
            $body['search_after'] = $after;
        }

        try {
            $response = $this->toArray($client->search(['body' => $body]));
        } catch (ClientResponseException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                throw new InvalidCursorException('This cursor has expired. Start again from the first page.', 0, $exception);
            }

            throw $exception;
        }

        $pit = (string) ($response['pit_id'] ?? $pit);
        $hits = $response['hits']['hits'];
        $isLast = count($hits) < $size;

        if ($isLast) {
            $this->close($pit);
        }

        return [
            'items' => array_map(fn (array $hit): array => [
                'id' => (int) $hit['_id'],
                'name' => $hit['_source']['name'],
                'category' => $hit['_source']['category'],
                'brand' => $hit['_source']['brand'],
                'price' => $hit['_source']['price'],
                'rating' => $hit['_source']['rating'],
            ], $hits),
            'cursor' => $isLast ? null : $this->encode(['pit' => $pit, 'after' => end($hits)['sort'], 'sort' => $sort]),
            'took_ms' => $response['took'],
        ];
    }

    /**
     * Turn the paging state into an opaque, tamper-proof cursor.
     */
    public function encode(array $state): string
    {
        return Crypt::encryptString(json_encode($state, JSON_THROW_ON_ERROR));
    }

    /**
     * Read the paging state back from a cursor.
     */
    public function decode(string $cursor): array
    {
        try {
            $state = json_decode(Crypt::decryptString($cursor), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException $exception) {
            throw new InvalidCursorException('Invalid cursor.', 0, $exception);
        }

        if (! is_array($state)
            || ! is_string($state['pit'] ?? null)
            || ! is_array($state['after'] ?? null)
            || ! array_key_exists($state['sort'] ?? '', self::SORTS)) {
            throw new InvalidCursorException('Invalid cursor.');
        }

        return $state;
    }

    /**
     * Release the point in time; an already expired one is fine.
     */
    private function close(string $pit): void
    {
        try {
            $this->clientBuilder->default()->closePointInTime(['body' => ['id' => $pit]]);
        } catch (ClientResponseException) {
            // The point in time already expired, nothing left to release.
        }
    }
}
