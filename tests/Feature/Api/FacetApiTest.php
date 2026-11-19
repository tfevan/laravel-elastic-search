<?php

namespace Tests\Feature\Api;

use App\Data\FacetSelection;
use App\Services\Search\FacetService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class FacetApiTest extends TestCase
{
    /**
     * Verify the request is mapped to a facet selection.
     */
    public function test_it_maps_the_request_to_a_selection(): void
    {
        $this->mock(FacetService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')
                ->once()
                ->with(Mockery::on(fn (FacetSelection $selection): bool => $selection->query === 'phone'
                    && $selection->categories === ['Audio', 'Phones']
                    && $selection->brands === ['Apex']
                    && $selection->priceRange === '50-200'
                    && $selection->minRating === 4.0))
                ->andReturn(['total' => 0, 'hits' => [], 'facets' => [], 'summary' => [], 'query' => []]);
        });

        $this->getJson('/api/v1/facets?'.http_build_query([
            'q' => 'phone',
            'categories' => ['Audio', 'Phones'],
            'brands' => ['Apex'],
            'price_range' => '50-200',
            'min_rating' => 4,
        ]))->assertOk()->assertJsonPath('data.total', 0);
    }

    /**
     * Verify unknown price ranges and ratings are rejected.
     */
    public function test_it_validates_the_selection(): void
    {
        $this->getJson('/api/v1/facets?price_range=free&min_rating=5&categories=Audio')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price_range', 'min_rating', 'categories']);
    }
}
