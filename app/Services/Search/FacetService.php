<?php

namespace App\Services\Search;

use App\Data\FacetSelection;
use App\Models\Product;
use App\Services\Elasticsearch\Concerns\ConvertsResponses;
use Elastic\Client\ClientBuilderInterface;
use stdClass;

class FacetService
{
    use ConvertsResponses;

    public const PRICE_RANGES = [
        'under-50' => ['label' => 'Under 50', 'from' => null, 'to' => 50],
        '50-200' => ['label' => '50 to 200', 'from' => 50, 'to' => 200],
        '200-500' => ['label' => '200 to 500', 'from' => 200, 'to' => 500],
        '500-1000' => ['label' => '500 to 1000', 'from' => 500, 'to' => 1000],
        '1000-plus' => ['label' => '1000 and more', 'from' => 1000, 'to' => null],
    ];

    public const RATINGS = [
        '4' => '4 stars and up',
        '3' => '3 stars and up',
        '2' => '2 stars and up',
    ];

    private const PAGE_SIZE = 12;

    /**
     * Create a new service instance.
     */
    public function __construct(private readonly ClientBuilderInterface $clientBuilder) {}

    /**
     * Build the search request: text query, post filter and one aggregation per facet.
     */
    public function requestBody(FacetSelection $selection): array
    {
        $body = [
            'size' => self::PAGE_SIZE,
            '_source' => ['name', 'description', 'category', 'brand', 'price', 'rating', 'tags'],
            'sort' => [['_score' => 'desc'], ['rating' => 'desc']],
            'query' => $selection->query === ''
                ? ['match_all' => new stdClass]
                : ['multi_match' => ['query' => $selection->query, 'fields' => ['name^3', 'description']]],
            'aggs' => [
                'categories' => $this->termsFacet('category', $this->clauses($selection, 'categories')),
                'brands' => $this->termsFacet('brand', $this->clauses($selection, 'brands')),
                'price_ranges' => $this->rangeFacet('price', $this->priceBuckets(), $this->clauses($selection, 'price')),
                'ratings' => $this->rangeFacet('rating', $this->ratingBuckets(), $this->clauses($selection, 'rating')),
                'summary' => [
                    'filter' => ['bool' => ['filter' => $this->clauses($selection)]],
                    'aggs' => [
                        'average_rating' => ['avg' => ['field' => 'rating']],
                        'price_stats' => ['stats' => ['field' => 'price']],
                    ],
                ],
            ],
        ];

        $clauses = $this->clauses($selection);

        if ($clauses !== []) {
            $body['post_filter'] = ['bool' => ['filter' => $clauses]];
        }

        return $body;
    }

    /**
     * Run the faceted search and return the hits, the facet counts and a summary.
     */
    public function search(FacetSelection $selection): array
    {
        $body = $this->requestBody($selection);

        $response = $this->toArray($this->clientBuilder->default()->search([
            'index' => (new Product)->searchableAs(),
            'body' => $body,
        ]));

        $aggregations = $response['aggregations'];

        return [
            'total' => $response['hits']['total']['value'],
            'hits' => array_map(fn (array $hit): array => [
                'id' => (int) $hit['_id'],
                'score' => $hit['_score'],
                'name' => $hit['_source']['name'],
                'description' => $hit['_source']['description'],
                'category' => $hit['_source']['category'],
                'brand' => $hit['_source']['brand'],
                'price' => $hit['_source']['price'],
                'rating' => $hit['_source']['rating'],
                'tags' => $hit['_source']['tags'],
            ], $response['hits']['hits']),
            'facets' => [
                'categories' => $this->buckets($aggregations['categories']['values']['buckets']),
                'brands' => $this->buckets($aggregations['brands']['values']['buckets']),
                'price_ranges' => $this->buckets($aggregations['price_ranges']['values']['buckets'], array_column(self::PRICE_RANGES, 'label')),
                'ratings' => $this->buckets($aggregations['ratings']['values']['buckets'], self::RATINGS),
            ],
            'summary' => [
                'average_rating' => $aggregations['summary']['average_rating']['value'],
                'min_price' => $aggregations['summary']['price_stats']['min'],
                'max_price' => $aggregations['summary']['price_stats']['max'],
            ],
            'query' => $body,
        ];
    }

    /**
     * Collect the filter clauses of every selected facet, optionally leaving one facet out.
     */
    private function clauses(FacetSelection $selection, ?string $except = null): array
    {
        $clauses = [];

        if ($except !== 'categories' && $selection->categories !== []) {
            $clauses[] = ['terms' => ['category' => $selection->categories]];
        }

        if ($except !== 'brands' && $selection->brands !== []) {
            $clauses[] = ['terms' => ['brand' => $selection->brands]];
        }

        if ($except !== 'price' && $selection->priceRange !== null) {
            $range = self::PRICE_RANGES[$selection->priceRange];

            $clauses[] = ['range' => ['price' => array_filter([
                'gte' => $range['from'],
                'lt' => $range['to'],
            ], fn (?int $value): bool => $value !== null)]];
        }

        if ($except !== 'rating' && $selection->minRating !== null) {
            $clauses[] = ['range' => ['rating' => ['gte' => $selection->minRating]]];
        }

        return $clauses;
    }

    /**
     * Build a terms facet counted inside the given filter clauses.
     */
    private function termsFacet(string $field, array $clauses): array
    {
        return [
            'filter' => ['bool' => ['filter' => $clauses]],
            'aggs' => ['values' => ['terms' => ['field' => $field, 'size' => 20, 'min_doc_count' => 0]]],
        ];
    }

    /**
     * Build a range facet counted inside the given filter clauses.
     */
    private function rangeFacet(string $field, array $ranges, array $clauses): array
    {
        return [
            'filter' => ['bool' => ['filter' => $clauses]],
            'aggs' => ['values' => ['range' => ['field' => $field, 'ranges' => $ranges]]],
        ];
    }

    /**
     * Describe the price buckets for the range aggregation.
     */
    private function priceBuckets(): array
    {
        $buckets = [];

        foreach (self::PRICE_RANGES as $key => $range) {
            $buckets[] = array_filter(['key' => $key, 'from' => $range['from'], 'to' => $range['to']], fn (int|string|null $value): bool => $value !== null);
        }

        return $buckets;
    }

    /**
     * Describe the rating buckets for the range aggregation.
     */
    private function ratingBuckets(): array
    {
        $buckets = [];

        foreach (array_keys(self::RATINGS) as $key) {
            $buckets[] = ['key' => (string) $key, 'from' => (int) $key];
        }

        return $buckets;
    }

    /**
     * Convert aggregation buckets to key, label and count entries.
     */
    private function buckets(array $buckets, array $labels = []): array
    {
        return array_map(fn (array $bucket): array => [
            'key' => (string) $bucket['key'],
            'label' => $labels[$bucket['key']] ?? (string) $bucket['key'],
            'count' => $bucket['doc_count'],
        ], $buckets);
    }
}
