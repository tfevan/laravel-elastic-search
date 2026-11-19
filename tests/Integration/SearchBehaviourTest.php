<?php

namespace Tests\Integration;

use App\Models\Product;
use Elastic\ScoutDriverPlus\Support\Query;

class SearchBehaviourTest extends ElasticsearchTestCase
{
    /**
     * Verify filters narrow the text matches without affecting relevance.
     */
    public function test_a_bool_query_combines_text_and_filters(): void
    {
        $this->createProduct(['name' => 'Office Chair', 'category' => 'furniture', 'price' => 120]);
        $this->createProduct(['name' => 'Gaming Chair', 'category' => 'furniture', 'price' => 400]);
        $this->createProduct(['name' => 'Chair Cushion', 'category' => 'home', 'price' => 20]);

        $query = Query::bool()
            ->must(Query::match()->field('name')->query('chair'))
            ->filter(Query::term()->field('category')->value('furniture'))
            ->filter(Query::range()->field('price')->lte('200'));

        $names = Product::searchQuery($query)->execute()->models()->pluck('name')->all();

        $this->assertSame(['Office Chair'], $names);
    }

    /**
     * Verify a typo still finds the product when fuzziness is on.
     */
    public function test_fuzziness_tolerates_a_typo(): void
    {
        $this->createProduct(['name' => 'Mechanical Keyboard']);

        $result = Product::searchQuery(Query::match()->field('name')->query('keybord')->fuzziness('AUTO'))->execute();

        $this->assertCount(1, $result->models());
    }

    /**
     * Verify the edge n-gram field matches a prefix.
     */
    public function test_autocomplete_matches_a_prefix(): void
    {
        $this->createProduct(['name' => 'Mechanical Keyboard']);

        $result = Product::searchQuery(Query::match()->field('name.autocomplete')->query('mech'))->execute();

        $this->assertCount(1, $result->models());
    }

    /**
     * Verify highlighting wraps the matched term.
     */
    public function test_highlighting_marks_the_match(): void
    {
        $this->createProduct(['name' => 'Wireless Mouse']);

        $result = Product::searchQuery(Query::match()->field('name')->query('mouse'))
            ->highlight('name')
            ->execute();

        $snippets = $result->hits()->first()->highlight()->snippets('name');

        $this->assertStringContainsString('<em>Mouse</em>', $snippets[0]);
    }

    /**
     * Verify the keyword sub-field supports exact aggregations.
     */
    public function test_a_terms_aggregation_counts_categories(): void
    {
        $this->createProduct(['category' => 'audio']);
        $this->createProduct(['category' => 'audio']);
        $this->createProduct(['category' => 'video']);

        $response = $this->client()->search([
            'index' => 'test_products',
            'body' => ['size' => 0, 'aggs' => ['categories' => ['terms' => ['field' => 'category']]]],
        ])->asArray();

        $buckets = array_column($response['aggregations']['categories']['buckets'], 'doc_count', 'key');

        $this->assertSame(['audio' => 2, 'video' => 1], $buckets);
    }
}
