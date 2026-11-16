<?php

namespace Tests\Feature\Api;

use App\Data\RelevanceOptions;
use App\Models\Product;
use App\Services\Search\RelevanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class RelevanceApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify the request is mapped to relevance options.
     */
    public function test_it_passes_the_options_to_the_service(): void
    {
        $this->mock(RelevanceService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')
                ->once()
                ->with(Mockery::on(fn (RelevanceOptions $options): bool => $options->query === 'headphones'
                    && $options->nameBoost === 5.0
                    && $options->operator === 'and'
                    && $options->preferTopRated
                    && ! $options->preferPopular
                    && $options->category === 'Audio'
                    && $options->excludeBrand === 'Nova'
                    && $options->maxPrice === 300.0))
                ->andReturn(['total' => 0, 'query' => [], 'hits' => []]);
        });

        $this->getJson('/api/v1/relevance?'.http_build_query([
            'q' => 'headphones',
            'name_boost' => 5,
            'operator' => 'and',
            'prefer_top_rated' => 1,
            'prefer_popular' => 0,
            'category' => 'Audio',
            'exclude_brand' => 'Nova',
            'max_price' => 300,
        ]))
            ->assertOk()
            ->assertJsonPath('data.total', 0);
    }

    /**
     * Verify out of range boosts and unknown operators are rejected.
     */
    public function test_it_validates_the_options(): void
    {
        $this->getJson('/api/v1/relevance?name_boost=50&operator=xor')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name_boost', 'operator']);
    }

    /**
     * Verify the explanation of a hit is returned.
     */
    public function test_it_explains_a_score(): void
    {
        $product = Product::factory()->create();

        $this->mock(RelevanceService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('explain')->once()->andReturn([
                'matched' => true,
                'explanation' => ['value' => 1.5, 'description' => 'sum of:'],
            ]);
        });

        $this->getJson("/api/v1/relevance/{$product->id}/explain?q=phone")
            ->assertOk()
            ->assertJsonPath('data.matched', true)
            ->assertJsonPath('data.explanation.value', 1.5);
    }

    /**
     * Verify a product missing from the index answers 404.
     */
    public function test_it_answers_not_found_for_a_product_that_is_not_indexed(): void
    {
        $product = Product::factory()->create();

        $this->mock(RelevanceService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('explain')->once()->andReturn(null);
        });

        $this->getJson("/api/v1/relevance/{$product->id}/explain")->assertNotFound();
    }
}
