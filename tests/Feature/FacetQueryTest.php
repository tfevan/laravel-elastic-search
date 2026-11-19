<?php

namespace Tests\Feature;

use App\Data\FacetSelection;
use App\Services\Search\FacetService;
use Tests\TestCase;

class FacetQueryTest extends TestCase
{
    /**
     * Verify the post filter applies every selected facet to the hits only.
     */
    public function test_the_post_filter_holds_all_selected_facets(): void
    {
        $body = $this->body(new FacetSelection(categories: ['Audio'], brands: ['Apex']));

        $postFilter = json_encode($body['post_filter'], JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('Audio', $postFilter);
        $this->assertStringContainsString('Apex', $postFilter);
    }

    /**
     * Verify each facet is counted without its own filter but with the others.
     */
    public function test_each_facet_ignores_its_own_filter(): void
    {
        $body = $this->body(new FacetSelection(categories: ['Audio'], brands: ['Apex']));

        $categories = json_encode($body['aggs']['categories']['filter'], JSON_THROW_ON_ERROR);
        $brands = json_encode($body['aggs']['brands']['filter'], JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString('Audio', $categories);
        $this->assertStringContainsString('Apex', $categories);
        $this->assertStringContainsString('Audio', $brands);
        $this->assertStringNotContainsString('Apex', $brands);
    }

    /**
     * Verify the summary statistics follow every selected facet.
     */
    public function test_the_summary_uses_all_filters(): void
    {
        $body = $this->body(new FacetSelection(categories: ['Audio'], brands: ['Apex'], minRating: 4.0));

        $summary = json_encode($body['aggs']['summary']['filter'], JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('Audio', $summary);
        $this->assertStringContainsString('Apex', $summary);
        $this->assertStringContainsString('rating', $summary);
    }

    /**
     * Verify no post filter is sent when nothing is selected.
     */
    public function test_there_is_no_post_filter_without_a_selection(): void
    {
        $this->assertArrayNotHasKey('post_filter', $this->body(new FacetSelection));
    }

    /**
     * Build the request body for the given selection.
     */
    private function body(FacetSelection $selection): array
    {
        return app(FacetService::class)->requestBody($selection);
    }
}
