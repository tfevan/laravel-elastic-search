<?php

namespace App\Services\Search;

use App\Data\FuzzyOptions;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\Elasticsearch\Concerns\ConvertsResponses;
use Elastic\Client\ClientBuilderInterface;
use Elastic\ScoutDriverPlus\Builders\MultiMatchQueryBuilder;
use Elastic\ScoutDriverPlus\Support\Query;

class FuzzyService
{
    use ConvertsResponses;

    private const LEVELS = ['0', '1', '2', 'AUTO'];

    /**
     * Create a new service instance.
     */
    public function __construct(private readonly ClientBuilderInterface $clientBuilder) {}

    /**
     * Build the typo tolerant multi match query.
     */
    public function query(string $text, string $fuzziness, int $prefixLength): MultiMatchQueryBuilder
    {
        return Query::multiMatch()
            ->fields(['name^3', 'description'])
            ->query($text)
            ->fuzziness($fuzziness)
            ->prefixLength($prefixLength);
    }

    /**
     * Search with the chosen fuzziness and compare the match count at every level.
     */
    public function search(FuzzyOptions $options, int $limit = 10): array
    {
        $result = Product::searchQuery($this->query($options->query, $options->fuzziness, $options->prefixLength))
            ->size($limit)
            ->execute();

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

        $counts = [];

        foreach (self::LEVELS as $level) {
            $counts[$level] = $level === $options->fuzziness
                ? ($result->total() ?? 0)
                : $this->count($options->query, $level, $options->prefixLength);
        }

        return [
            'total' => $result->total() ?? 0,
            'hits' => $hits,
            'counts' => $counts,
            'suggestion' => $this->suggest($options->query),
        ];
    }

    /**
     * Suggest a corrected spelling for the text, or null when it looks right.
     */
    public function suggest(string $text): ?string
    {
        $response = $this->clientBuilder->default()->search([
            'index' => (new Product)->searchableAs(),
            'body' => [
                'size' => 0,
                'suggest' => [
                    'did_you_mean' => [
                        'text' => $text,
                        'phrase' => [
                            'field' => 'name',
                            'analyzer' => 'product_index',
                            'size' => 1,
                            'max_errors' => 2,
                            'direct_generator' => [
                                ['field' => 'name', 'suggest_mode' => 'missing'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $suggestion = $this->toArray($response)['suggest']['did_you_mean'][0]['options'][0]['text'] ?? null;

        if (! is_string($suggestion) || strtolower($suggestion) === strtolower($text)) {
            return null;
        }

        return $suggestion;
    }

    /**
     * Count the matches of a query without loading any hit.
     */
    private function count(string $text, string $fuzziness, int $prefixLength): int
    {
        return Product::searchQuery($this->query($text, $fuzziness, $prefixLength))->size(0)->execute()->total() ?? 0;
    }
}
