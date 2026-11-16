<?php

namespace Tests\Feature\Api;

use App\Data\ProductFilters;
use App\Models\Product;
use App\Services\Search\ProductFilterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class FilterApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify the request is mapped to a filter set and the results are returned.
     */
    public function test_it_passes_the_filters_to_the_service(): void
    {
        $product = Product::factory()->create(['name' => 'Apex Earbuds']);

        $this->mock(ProductFilterService::class, function (MockInterface $mock) use ($product): void {
            $mock->shouldReceive('filter')
                ->once()
                ->with(Mockery::on(fn (ProductFilters $filters): bool => $filters->query === 'wireless'
                    && $filters->category === 'Audio'
                    && $filters->brands === ['Apex', 'Nova']
                    && $filters->minPrice === 100.0
                    && $filters->maxPrice === 500.0
                    && $filters->sort === 'price_asc'))
                ->andReturn([$product]);
        });

        $this->getJson('/api/v1/filters?'.http_build_query([
            'q' => 'wireless',
            'category' => 'Audio',
            'brands' => ['Apex', 'Nova'],
            'min_price' => 100,
            'max_price' => 500,
            'sort' => 'price_asc',
        ]))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Apex Earbuds');
    }

    /**
     * Verify unknown sort values are rejected.
     */
    public function test_it_rejects_an_unknown_sort(): void
    {
        $this->getJson('/api/v1/filters?sort=random')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sort');
    }

    /**
     * Verify the maximum price cannot be below the minimum price.
     */
    public function test_it_rejects_an_inverted_price_range(): void
    {
        $this->getJson('/api/v1/filters?min_price=500&max_price=100')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('max_price');
    }

    /**
     * Verify a maximum price alone is accepted.
     */
    public function test_it_accepts_a_maximum_price_without_a_minimum(): void
    {
        $this->mock(ProductFilterService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('filter')->once()->andReturn([]);
        });

        $this->getJson('/api/v1/filters?max_price=100')->assertOk();
    }

    /**
     * Verify filter options list each category and brand once.
     */
    public function test_it_lists_distinct_filter_options(): void
    {
        Product::factory()->create(['category' => 'Phones', 'brand' => 'Nova']);
        Product::factory()->create(['category' => 'Phones', 'brand' => 'Apex']);
        Product::factory()->create(['category' => 'Audio', 'brand' => 'Apex']);

        $this->getJson('/api/v1/filters/options')
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'categories' => ['Audio', 'Phones'],
                    'brands' => ['Apex', 'Nova'],
                ],
            ]);
    }
}
