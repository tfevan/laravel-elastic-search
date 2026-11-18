<?php

namespace App\Services\Search;

use App\Models\Product;
use Elastic\ScoutDriverPlus\Builders\MatchQueryBuilder;
use Elastic\ScoutDriverPlus\Support\Query;

class AutocompleteService
{
    /**
     * Build the query that matches every typed word against the n-gram sub-field.
     */
    public function query(string $text): MatchQueryBuilder
    {
        return Query::match()
            ->field('name.autocomplete')
            ->query($text)
            ->operator('and');
    }

    /**
     * Return the products whose name starts with the typed words.
     */
    public function suggest(string $text, int $limit = 8): array
    {
        $result = Product::searchQuery($this->query($text))->size($limit)->execute();

        $suggestions = [];

        foreach ($result->hits() as $hit) {
            $document = $hit->document();
            $content = $document->content();

            $suggestions[] = [
                'id' => (int) $document->id(),
                'name' => $content['name'],
                'category' => $content['category'],
                'brand' => $content['brand'],
            ];
        }

        return $suggestions;
    }
}
