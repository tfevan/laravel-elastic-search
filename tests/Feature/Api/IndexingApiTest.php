<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexingApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify synchronous indexing is reported by default.
     */
    public function test_it_reports_synchronous_indexing(): void
    {
        $this->getJson('/api/v1/performance/indexing')
            ->assertOk()
            ->assertJsonPath('data.queued', false);
    }

    /**
     * Verify queued indexing is reported when Scout is configured to queue.
     */
    public function test_it_reports_queued_indexing(): void
    {
        config(['scout.queue' => true]);

        $this->getJson('/api/v1/performance/indexing')
            ->assertOk()
            ->assertJsonPath('data.queued', true);
    }

    /**
     * Verify touching products reports the count and the time taken.
     */
    public function test_it_touches_products_and_measures_the_time(): void
    {
        Product::factory()->count(12)->create();

        $this->postJson('/api/v1/performance/indexing/touch', ['count' => 10])
            ->assertOk()
            ->assertJsonPath('data.touched', 10)
            ->assertJsonStructure(['data' => ['queued', 'queue_connection', 'pending_jobs', 'elapsed_ms']]);
    }

    /**
     * Verify only the offered batch sizes are accepted.
     */
    public function test_it_validates_the_batch_size(): void
    {
        $this->postJson('/api/v1/performance/indexing/touch', ['count' => 7])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('count');
    }
}
