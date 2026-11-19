<?php

namespace Tests\Feature\Api;

use App\Data\GeoQuery;
use App\Services\Search\GeoService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class GeoApiTest extends TestCase
{
    /**
     * Verify the request is mapped to a geo query.
     */
    public function test_it_maps_the_request_to_a_geo_query(): void
    {
        $this->mock(GeoService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')
                ->once()
                ->with(Mockery::on(fn (GeoQuery $query): bool => $query->latitude === 23.79
                    && $query->longitude === 90.4
                    && $query->radiusKm === 2.5
                    && $query->type === 'Cafe'
                    && $query->query === 'gulshan'))
                ->andReturn(['total' => 0, 'stores' => [], 'rings' => [], 'query' => []]);
        });

        $this->getJson('/api/v1/geo?latitude=23.79&longitude=90.4&radius_km=2.5&type=Cafe&q=gulshan')
            ->assertOk()
            ->assertJsonPath('data.total', 0);
    }

    /**
     * Verify coordinates are required and must be in range.
     */
    public function test_it_validates_the_coordinates(): void
    {
        $this->getJson('/api/v1/geo?latitude=95&longitude=200&radius_km=900')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude', 'longitude', 'radius_km']);

        $this->getJson('/api/v1/geo')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }
}
