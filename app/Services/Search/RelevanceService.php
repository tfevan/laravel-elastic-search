<?php

namespace App\Services\Search;

use App\Data\RelevanceOptions;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\Elasticsearch\Concerns\ConvertsResponses;
use Elastic\Client\ClientBuilderInterface;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\ScoutDriverPlus\Builders\BoolQueryBuilder;
use Elastic\ScoutDriverPlus\Support\Query;
use Symfony\Component\HttpFoundation\Response;

class RelevanceService
{
    use ConvertsResponses;

    private const EXPLANATION_DEPTH = 4;

    /**
     * Create a new service instance.
     */
    public function __construct(private readonly ClientBuilderInterface $clientBuilder) {}

    /**
     * Build the bool query for the given options.
     */
    public function query(RelevanceOptions $options): BoolQueryBuilder
    {
        $bool = Query::bool();

        if ($options->query === '') {
            $bool->must(Query::matchAll());
        } else {
            $bool->must(
                Query::multiMatch()
                    ->fields(["name^{$options->nameBoost}", "description^{$options->descriptionBoost}"])
                    ->query($options->query)
                    ->operator($options->operator),
            );
        }

        if ($options->preferTopRated) {
            $bool->should(Query::range()->field('rating')->gte('4.5')->boost(2));
        }

        if ($options->preferPopular) {
            $bool->should(Query::term()->field('tags')->value('popular')->boost(1.5));
        }

        if ($options->category !== null) {
            $bool->filter(Query::term()->field('category')->value($options->category));
        }

        if ($options->excludeBrand !== null) {
            $bool->mustNot(Query::term()->field('brand')->value($options->excludeBrand));
        }

        if ($options->minPrice !== null || $options->maxPrice !== null) {
            $range = Query::range()->field('price');

            if ($options->minPrice !== null) {
                $range->gte((string) $options->minPrice);
            }

            if ($options->maxPrice !== null) {
                $range->lte((string) $options->maxPrice);
            }

            $bool->filter($range);
        }

        return $bool;
    }

    /**
     * Run the query and return the hits with their relevance scores.
     */
    public function search(RelevanceOptions $options, int $limit = 10): array
    {
        $query = $this->query($options);
        $result = Product::searchQuery($query)->size($limit)->execute();

        $hits = [];

        foreach ($result->hits() as $hit) {
            $model = $hit->model();

            if ($model === null) {
                continue;
            }

            $hits[] = [
                'id' => $model->getKey(),
                'score' => $hit->score(),
                'product' => (new ProductResource($model))->resolve(),
            ];
        }

        return [
            'total' => $result->total(),
            'query' => $query->buildQuery(),
            'hits' => $hits,
        ];
    }

    /**
     * Explain how the query scored one product, or return null when it is not indexed.
     */
    public function explain(Product $product, RelevanceOptions $options): ?array
    {
        try {
            $response = $this->clientBuilder->default()->explain([
                'index' => $product->searchableAs(),
                'id' => $product->getScoutKey(),
                'body' => ['query' => $this->query($options)->buildQuery()],
            ]);
        } catch (ClientResponseException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return null;
            }

            throw $exception;
        }

        $body = $this->toArray($response);

        return [
            'matched' => $body['matched'],
            'explanation' => isset($body['explanation']) ? $this->simplify($body['explanation'], self::EXPLANATION_DEPTH) : null,
        ];
    }

    /**
     * Keep only the value, description and nested details of an explanation node.
     */
    private function simplify(array $node, int $depth): array
    {
        $result = ['value' => $node['value'], 'description' => $node['description']];

        if ($depth > 0 && ! empty($node['details'])) {
            $result['details'] = array_map(fn (array $child): array => $this->simplify($child, $depth - 1), $node['details']);
        }

        return $result;
    }
}
