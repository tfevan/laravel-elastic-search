<?php

namespace Tests\Feature;

use App\Data\RelevanceOptions;
use App\Services\Search\RelevanceService;
use Tests\TestCase;

class RelevanceQueryTest extends TestCase
{
    /**
     * Verify the text query boosts the name field over the description.
     */
    public function test_it_boosts_the_name_field(): void
    {
        $json = $this->buildJson(new RelevanceOptions(query: 'phone'));

        $this->assertStringContainsString('multi_match', $json);
        $this->assertStringContainsString('name^3', $json);
        $this->assertStringContainsString('description^1', $json);
    }

    /**
     * Verify filters, exclusions and preferences land in their bool clauses.
     */
    public function test_it_places_conditions_in_the_right_clauses(): void
    {
        $dsl = app(RelevanceService::class)->query(new RelevanceOptions(
            query: 'phone',
            preferTopRated: true,
            category: 'Phones',
            excludeBrand: 'Nova',
            maxPrice: 500,
        ))->buildQuery();

        $this->assertArrayHasKey('must', $dsl['bool']);
        $this->assertArrayHasKey('should', $dsl['bool']);
        $this->assertArrayHasKey('filter', $dsl['bool']);
        $this->assertArrayHasKey('must_not', $dsl['bool']);
        $this->assertStringContainsString('Phones', json_encode($dsl['bool']['filter'], JSON_THROW_ON_ERROR));
        $this->assertStringContainsString('Nova', json_encode($dsl['bool']['must_not'], JSON_THROW_ON_ERROR));
    }

    /**
     * Verify an empty text becomes a match all query.
     */
    public function test_it_matches_everything_without_a_text(): void
    {
        $this->assertStringContainsString('match_all', $this->buildJson(new RelevanceOptions));
    }

    /**
     * Encode the query built for the given options.
     */
    private function buildJson(RelevanceOptions $options): string
    {
        return json_encode(app(RelevanceService::class)->query($options)->buildQuery(), JSON_THROW_ON_ERROR);
    }
}
