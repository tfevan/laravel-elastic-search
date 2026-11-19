<?php

namespace Tests\Feature;

use App\Services\Elasticsearch\AliasReindexService;
use Tests\TestCase;

class AliasReindexServiceTest extends TestCase
{
    /**
     * Verify the first version is numbered one.
     */
    public function test_the_first_index_is_version_one(): void
    {
        $this->assertSame('catalog_v1', app(AliasReindexService::class)->nextIndexName(null));
    }

    /**
     * Verify the next version follows the current one.
     */
    public function test_the_next_index_follows_the_current_version(): void
    {
        $service = app(AliasReindexService::class);

        $this->assertSame('catalog_v4', $service->nextIndexName('catalog_v3'));
        $this->assertSame('catalog_v11', $service->nextIndexName('catalog_v10'));
    }
}
