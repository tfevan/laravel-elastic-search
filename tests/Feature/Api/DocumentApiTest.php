<?php

namespace Tests\Feature\Api;

use App\Exceptions\InvalidSearchQueryException;
use App\Models\Product;
use App\Services\Elasticsearch\ProductIndexService;
use App\Services\Search\RawSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class DocumentApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify the document view lists the indexed fields and the stored document.
     */
    public function test_it_shows_the_searchable_array_and_the_indexed_document(): void
    {
        $product = Product::factory()->create();

        $this->mock(ProductIndexService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('document')->once()->andReturn(['name' => 'Stored name']);
        });

        $this->getJson("/api/v1/documents/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.index', 'products')
            ->assertJsonPath('data.scout_key', $product->id)
            ->assertJsonPath('data.should_be_searchable', true)
            ->assertJsonPath('data.indexed_document.name', 'Stored name')
            ->assertJsonStructure(['data' => ['searchable_array' => ['name', 'price', 'tags']]]);
    }

    /**
     * Verify an inactive product reports that it is not searchable.
     */
    public function test_it_marks_an_inactive_product_as_not_searchable(): void
    {
        $product = Product::factory()->create(['is_active' => false]);

        $this->mock(ProductIndexService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('document')->once()->andReturn(null);
        });

        $this->getJson("/api/v1/documents/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.should_be_searchable', false)
            ->assertJsonPath('data.indexed_document', null);
    }

    /**
     * Verify the raw search returns hits with scores.
     */
    public function test_it_returns_raw_hits(): void
    {
        $this->mock(RawSearchService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')->once()->with('phone')->andReturn([
                'total' => 1,
                'hits' => [['id' => '7', 'score' => 1.5, 'source' => ['name' => 'Apex Smartphone']]],
            ]);
        });

        $this->getJson('/api/v1/documents/raw?q=phone')
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.hits.0.score', 1.5);
    }

    /**
     * Verify broken query syntax answers 422.
     */
    public function test_it_reports_invalid_raw_query_syntax(): void
    {
        $this->mock(RawSearchService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')->once()->andThrow(new InvalidSearchQueryException('Invalid search syntax.'));
        });

        $this->getJson('/api/v1/documents/raw?q=name:(phone')->assertUnprocessable();
    }

    /**
     * Verify a product can be deactivated and reactivated.
     */
    public function test_it_toggles_the_activation_flag(): void
    {
        $product = Product::factory()->create();

        $this->patchJson("/api/v1/products/{$product->id}/activation", ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($product->refresh()->is_active);

        $this->patchJson("/api/v1/products/{$product->id}/activation", ['is_active' => true])->assertOk();

        $this->assertTrue($product->refresh()->is_active);
    }

    /**
     * Verify the activation flag is required.
     */
    public function test_it_requires_the_activation_flag(): void
    {
        $product = Product::factory()->create();

        $this->patchJson("/api/v1/products/{$product->id}/activation", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('is_active');
    }
}
