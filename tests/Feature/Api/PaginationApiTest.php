<?php

namespace Tests\Feature\Api;

use App\Data\ProductPage;
use App\Models\Product;
use App\Services\Search\ProductPaginationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class PaginationApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify the response carries the pagination metadata.
     */
    public function test_it_returns_a_page_with_metadata(): void
    {
        $product = Product::factory()->create();

        $this->mock(ProductPaginationService::class, function (MockInterface $mock) use ($product): void {
            $mock->shouldReceive('paginate')->once()->with('phone', 10)->andReturn(new ProductPage([$product], 95, 1, 10));
        });

        $this->getJson('/api/v1/pagination?q=phone&per_page=10&page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 95)
            ->assertJsonPath('meta.last_page', 10)
            ->assertJsonPath('meta.per_page', 10);
    }

    /**
     * Verify unsupported page sizes are rejected.
     */
    public function test_it_rejects_an_unsupported_page_size(): void
    {
        $this->getJson('/api/v1/pagination?per_page=7')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }

    /**
     * Verify the last page inside the result window is accepted.
     */
    public function test_it_accepts_the_last_page_inside_the_result_window(): void
    {
        $this->mock(ProductPaginationService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('paginate')->once()->andReturn(new ProductPage([], 0, 1000, 10));
        });

        $this->getJson('/api/v1/pagination?page=1000&per_page=10')->assertOk();
    }

    /**
     * Verify pages beyond the 10,000 result window are rejected.
     */
    public function test_it_rejects_pages_beyond_the_result_window(): void
    {
        $this->getJson('/api/v1/pagination?page=1001&per_page=10')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('page');
    }
}
