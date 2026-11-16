<?php

namespace Tests\Feature\Api;

use App\Exceptions\InvalidSearchQueryException;
use App\Models\Product;
use App\Services\Search\ProductSearchService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify matching products are returned.
     */
    public function test_it_returns_search_results(): void
    {
        $product = Product::factory()->create(['name' => 'Apex Wireless Headphones']);

        $this->mock(ProductSearchService::class, function (MockInterface $mock) use ($product): void {
            $mock->shouldReceive('search')->once()->with('wireless')->andReturn([$product]);
        });

        $this->getJson('/api/v1/search?q=wireless')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Apex Wireless Headphones');
    }

    /**
     * Verify an empty query is accepted.
     */
    public function test_it_accepts_an_empty_query(): void
    {
        $this->mock(ProductSearchService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')->once()->with('')->andReturn([]);
        });

        $this->getJson('/api/v1/search')->assertOk()->assertJsonCount(0, 'data');
    }

    /**
     * Verify an overly long query is rejected.
     */
    public function test_it_rejects_a_too_long_query(): void
    {
        $this->getJson('/api/v1/search?q='.str_repeat('a', 201))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }

    /**
     * Verify broken query syntax answers 422.
     */
    public function test_it_reports_invalid_query_syntax(): void
    {
        $this->mock(ProductSearchService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')->once()->andThrow(new InvalidSearchQueryException('Invalid search syntax.'));
        });

        $this->getJson('/api/v1/search?q=name:(phone')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Invalid search syntax.');
    }

    /**
     * Verify an unreachable Elasticsearch answers 503.
     */
    public function test_it_reports_an_unreachable_elasticsearch(): void
    {
        $this->mock(ProductSearchService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')->once()->andThrow(new NoNodeAvailableException('No node available'));
        });

        $this->getJson('/api/v1/search?q=phone')->assertStatus(503);
    }
}
