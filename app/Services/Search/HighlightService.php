<?php

namespace App\Services\Search;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Elastic\ScoutDriverPlus\Builders\MultiMatchQueryBuilder;
use Elastic\ScoutDriverPlus\Support\Query;

class HighlightService
{
    public const START_MARKER = '[[hl]]';

    public const END_MARKER = '[[/hl]]';

    /**
     * Build the multi match query, optionally tolerant to typos.
     */
    public function query(string $text, bool $fuzzy): MultiMatchQueryBuilder
    {
        $query = Query::multiMatch()->fields(['name^3', 'description'])->query($text);

        if ($fuzzy) {
            $query->fuzziness('AUTO')->prefixLength(1);
        }

        return $query;
    }

    /**
     * Search products and return the hits with highlighted name and description snippets.
     */
    public function search(string $text, bool $fuzzy, int $limit = 10): array
    {
        $markers = ['pre_tags' => [self::START_MARKER], 'post_tags' => [self::END_MARKER]];

        $result = Product::searchQuery($this->query($text, $fuzzy))
            ->highlight('name', $markers + ['number_of_fragments' => 0])
            ->highlight('description', $markers + ['number_of_fragments' => 1, 'fragment_size' => 120])
            ->size($limit)
            ->execute();

        $hits = [];

        foreach ($result->hits() as $hit) {
            $model = $hit->model();

            if ($model === null) {
                continue;
            }

            $highlight = $hit->highlight();

            $hits[] = [
                'id' => $model->getKey(),
                'score' => $hit->score(),
                'product' => (new ProductResource($model))->resolve(),
                'highlight' => [
                    'name' => $highlight?->snippets('name')?->first(),
                    'description' => $highlight?->snippets('description')?->first(),
                ],
            ];
        }

        return [
            'total' => $result->total() ?? 0,
            'markers' => ['start' => self::START_MARKER, 'end' => self::END_MARKER],
            'hits' => $hits,
        ];
    }
}
