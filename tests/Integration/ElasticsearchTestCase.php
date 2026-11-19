<?php

namespace Tests\Integration;

use App\Models\Product;
use App\Models\Store;
use Elastic\Client\ClientBuilderInterface;
use Elastic\Elasticsearch\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Throwable;

#[Group('elasticsearch')]
abstract class ElasticsearchTestCase extends TestCase
{
    use RefreshDatabase;

    private const TEST_PREFIX = 'test_';

    private static bool $indicesPrepared = false;

    /**
     * Prepare empty test indices before every test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->guardAgainstDevelopmentIndices();
        $this->requireElasticsearch();

        if (! self::$indicesPrepared) {
            Artisan::call('elastic:migrate');
            self::$indicesPrepared = true;
        }

        $this->assertTrue(
            $this->client()->indices()->exists(['index' => (new Product)->searchableAs()])->asBool(),
            'The test index does not exist. Check that elastic migrations apply the index name prefix.',
        );

        Product::removeAllFromSearch();
        Store::removeAllFromSearch();
        $this->refreshIndices();
    }

    /**
     * Get the Elasticsearch client.
     */
    protected function client(): Client
    {
        return $this->app->make(ClientBuilderInterface::class)->default();
    }

    /**
     * Make newly indexed documents visible to search.
     */
    protected function refreshIndices(): void
    {
        $this->client()->indices()->refresh(['index' => self::TEST_PREFIX.'*']);
    }

    /**
     * Create and index a product, then refresh so it can be searched.
     */
    protected function createProduct(array $attributes = []): Product
    {
        $product = Product::factory()->create($attributes);

        $this->refreshIndices();

        return $product;
    }

    /**
     * Stop immediately when the tests would touch the development indices.
     */
    private function guardAgainstDevelopmentIndices(): void
    {
        if (config('scout.prefix') !== self::TEST_PREFIX) {
            $this->fail('Integration tests must run with SCOUT_PREFIX=test_. Use phpunit.integration.xml.');
        }
    }

    /**
     * Skip locally when Elasticsearch is down, but fail in CI where it must be up.
     */
    private function requireElasticsearch(): void
    {
        try {
            $reachable = $this->client()->ping()->asBool();
        } catch (Throwable) {
            $reachable = false;
        }

        if ($reachable) {
            return;
        }

        if (getenv('CI') !== false) {
            $this->fail('Elasticsearch is not reachable in CI.');
        }

        $this->markTestSkipped('Elasticsearch is not reachable.');
    }
}
