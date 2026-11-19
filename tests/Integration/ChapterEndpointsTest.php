<?php

namespace Tests\Integration;

use App\Models\Product;
use App\Services\Elasticsearch\AliasReindexService;

class ChapterEndpointsTest extends ElasticsearchTestCase
{
    /**
     * Verify search_after walks every product exactly once.
     */
    public function test_deep_pagination_visits_every_product_once(): void
    {
        Product::factory()->count(25)->create();
        $this->refreshIndices();

        $ids = [];
        $cursor = null;
        $pages = 0;

        do {
            $query = ['size' => 10] + ($cursor === null ? [] : ['cursor' => $cursor]);
            $response = $this->getJson('/api/v1/performance/deep-pagination?'.http_build_query($query))->assertOk();

            array_push($ids, ...array_column($response->json('data.items'), 'id'));
            $cursor = $response->json('data.cursor');
            $pages++;
        } while ($cursor !== null && $pages < 10);

        $this->assertSame(3, $pages);
        $this->assertCount(25, $ids);
        $this->assertSame($ids, array_values(array_unique($ids)));
    }

    /**
     * Verify the alias moves forward on reindex and back on rollback.
     */
    public function test_alias_reindex_switches_versions_and_rolls_back(): void
    {
        Product::factory()->count(5)->create();
        $this->refreshIndices();

        $service = $this->app->make(AliasReindexService::class);
        $service->reset();

        try {
            $first = $service->reindex();
            $this->assertSame('catalog_v1', $first['to']);
            $this->assertSame(5, $first['copied']);

            $second = $service->reindex();
            $this->assertSame('catalog_v2', $second['status']['index']);
            $this->assertSame(5, $second['status']['documents']);

            $this->assertSame('catalog_v1', $service->rollback()['index']);
        } finally {
            $service->reset();
        }
    }
}
