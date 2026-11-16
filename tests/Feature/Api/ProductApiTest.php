<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Services\Elasticsearch\ProductIndexService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a valid product payload.
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Apex Smartphone AB-123',
            'description' => 'Lightweight design with all-day battery life.',
            'category' => 'Phones',
            'brand' => 'Apex',
            'price' => 499.99,
            'rating' => 4.5,
            'tags' => ['new', 'popular'],
        ], $overrides);
    }

    /**
     * Verify the list is paginated.
     */
    public function test_it_lists_products_with_pagination(): void
    {
        Product::factory()->count(12)->create();

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 12);
    }

    /**
     * Verify a product can be created.
     */
    public function test_it_creates_a_product(): void
    {
        $this->postJson('/api/v1/products', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Apex Smartphone AB-123');

        $this->assertDatabaseCount('products', 1);
    }

    /**
     * Verify invalid input is rejected.
     */
    public function test_it_validates_the_payload(): void
    {
        $this->postJson('/api/v1/products', $this->payload(['name' => '', 'rating' => 9]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'rating']);
    }

    /**
     * Verify a product can be updated.
     */
    public function test_it_updates_a_product(): void
    {
        $product = Product::factory()->create();

        $this->putJson("/api/v1/products/{$product->id}", $this->payload(['brand' => 'Nova']))
            ->assertOk()
            ->assertJsonPath('data.brand', 'Nova');
    }

    /**
     * Verify a product can be deleted.
     */
    public function test_it_deletes_a_product(): void
    {
        $product = Product::factory()->create();

        $this->deleteJson("/api/v1/products/{$product->id}")->assertNoContent();

        $this->assertModelMissing($product);
    }

    /**
     * Verify the index summary compares both stores.
     */
    public function test_it_returns_the_index_summary(): void
    {
        Product::factory()->count(3)->create();

        $this->mock(ProductIndexService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('count')->once()->andReturn(3);
        });

        $this->getJson('/api/v1/products/index-summary')
            ->assertOk()
            ->assertJsonPath('data.database', 3)
            ->assertJsonPath('data.elasticsearch', 3);
    }

    /**
     * Verify a missing index document answers 404.
     */
    public function test_it_returns_not_found_for_a_product_that_is_not_indexed(): void
    {
        $product = Product::factory()->create();

        $this->mock(ProductIndexService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('document')->once()->andReturn(null);
        });

        $this->getJson("/api/v1/products/{$product->id}/document")->assertNotFound();
    }
}
