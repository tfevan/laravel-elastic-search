<?php

namespace Tests\Feature;

use App\Data\GeoQuery;
use App\Models\Store;
use App\Services\Search\GeoService;
use Tests\TestCase;

class GeoQueryTest extends TestCase
{
    /**
     * Verify the radius is a post filter so the rings still count every store.
     */
    public function test_the_radius_is_a_post_filter(): void
    {
        $body = app(GeoService::class)->requestBody(new GeoQuery(23.79, 90.4, 2.5));

        $this->assertSame('2.5km', $body['post_filter']['geo_distance']['distance']);
        $this->assertSame(['lat' => 23.79, 'lon' => 90.4], $body['post_filter']['geo_distance']['location']);
        $this->assertArrayHasKey('rings', $body['aggs']);
        $this->assertStringNotContainsString('2.5km', json_encode($body['query'], JSON_THROW_ON_ERROR));
    }

    /**
     * Verify the results are sorted by distance, nearest first.
     */
    public function test_it_sorts_by_distance(): void
    {
        $body = app(GeoService::class)->requestBody(new GeoQuery(23.79, 90.4));

        $this->assertSame('asc', $body['sort'][0]['_geo_distance']['order']);
        $this->assertSame('km', $body['sort'][0]['_geo_distance']['unit']);
    }

    /**
     * Verify the type filter is only added when a type is chosen.
     */
    public function test_the_type_filter_is_optional(): void
    {
        $service = app(GeoService::class);

        $this->assertSame([], $service->requestBody(new GeoQuery(23.79, 90.4))['query']['bool']['filter']);
        $this->assertSame(
            [['term' => ['type' => 'Cafe']]],
            $service->requestBody(new GeoQuery(23.79, 90.4, type: 'Cafe'))['query']['bool']['filter'],
        );
    }

    /**
     * Verify a store is indexed with a lat/lon location.
     */
    public function test_a_store_exposes_its_location_for_indexing(): void
    {
        $store = new Store([
            'name' => 'Gulshan Cafe',
            'type' => 'Cafe',
            'area' => 'Gulshan',
            'city' => 'Dhaka',
            'latitude' => 23.7925,
            'longitude' => 90.4078,
        ]);

        $this->assertSame(['lat' => 23.7925, 'lon' => 90.4078], $store->toSearchableArray()['location']);
    }
}
