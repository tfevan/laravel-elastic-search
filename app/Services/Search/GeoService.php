<?php

namespace App\Services\Search;

use App\Data\GeoQuery;
use App\Models\Store;
use App\Services\Elasticsearch\Concerns\ConvertsResponses;
use Elastic\Client\ClientBuilderInterface;
use stdClass;

class GeoService
{
    use ConvertsResponses;

    private const RINGS = [
        'Under 1 km' => ['to' => 1],
        '1 to 3 km' => ['from' => 1, 'to' => 3],
        '3 to 5 km' => ['from' => 3, 'to' => 5],
        'Over 5 km' => ['from' => 5],
    ];

    private const SIZE = 20;

    /**
     * Create a new service instance.
     */
    public function __construct(private readonly ClientBuilderInterface $clientBuilder) {}

    /**
     * Build the request: text and type filters, a radius post filter, distance sort and distance rings.
     */
    public function requestBody(GeoQuery $query): array
    {
        $origin = ['lat' => $query->latitude, 'lon' => $query->longitude];

        $filters = $query->type === null ? [] : [['term' => ['type' => $query->type]]];

        return [
            'size' => self::SIZE,
            '_source' => ['name', 'type', 'area', 'city', 'location'],
            'query' => ['bool' => [
                'must' => [
                    $query->query === ''
                        ? ['match_all' => new stdClass]
                        : ['match' => ['name' => $query->query]],
                ],
                'filter' => $filters,
            ]],
            'post_filter' => ['geo_distance' => [
                'distance' => "{$query->radiusKm}km",
                'location' => $origin,
            ]],
            'sort' => [['_geo_distance' => [
                'location' => $origin,
                'order' => 'asc',
                'unit' => 'km',
            ]]],
            'aggs' => ['rings' => ['geo_distance' => [
                'field' => 'location',
                'origin' => $origin,
                'unit' => 'km',
                'ranges' => array_values(self::RINGS),
            ]]],
        ];
    }

    /**
     * Find the stores nearest to a point.
     */
    public function search(GeoQuery $query): array
    {
        $body = $this->requestBody($query);

        $response = $this->toArray($this->clientBuilder->default()->search([
            'index' => (new Store)->searchableAs(),
            'body' => $body,
        ]));

        return [
            'total' => $response['hits']['total']['value'],
            'stores' => array_map(fn (array $hit): array => [
                'id' => (int) $hit['_id'],
                'name' => $hit['_source']['name'],
                'type' => $hit['_source']['type'],
                'area' => $hit['_source']['area'],
                'city' => $hit['_source']['city'],
                'latitude' => $hit['_source']['location']['lat'],
                'longitude' => $hit['_source']['location']['lon'],
                'distance_km' => round((float) $hit['sort'][0], 2),
            ], $response['hits']['hits']),
            'rings' => $this->rings($response['aggregations']['rings']['buckets']),
            'query' => $body,
        ];
    }

    /**
     * Pair each distance bucket with its label.
     */
    private function rings(array $buckets): array
    {
        $labels = array_keys(self::RINGS);
        $rings = [];

        foreach ($buckets as $index => $bucket) {
            $rings[] = ['label' => $labels[$index], 'count' => $bucket['doc_count']];
        }

        return $rings;
    }
}
