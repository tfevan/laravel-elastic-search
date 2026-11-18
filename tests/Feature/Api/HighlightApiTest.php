<?php

namespace Tests\Feature\Api;

use App\Services\Search\HighlightService;
use Mockery\MockInterface;
use Tests\TestCase;

class HighlightApiTest extends TestCase
{
    /**
     * Verify the highlighted hits are returned.
     */
    public function test_it_returns_highlighted_hits(): void
    {
        $this->mock(HighlightService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')->once()->with('mobile', false)->andReturn([
                'total' => 1,
                'markers' => ['start' => '[[hl]]', 'end' => '[[/hl]]'],
                'hits' => [[
                    'id' => 1,
                    'score' => 2.0,
                    'product' => ['id' => 1, 'name' => 'Apex Smartphone'],
                    'highlight' => ['name' => 'Apex [[hl]]Smartphone[[/hl]]', 'description' => null],
                ]],
            ]);
        });

        $this->getJson('/api/v1/highlight?q=mobile')
            ->assertOk()
            ->assertJsonPath('data.markers.start', '[[hl]]')
            ->assertJsonPath('data.hits.0.highlight.name', 'Apex [[hl]]Smartphone[[/hl]]');
    }

    /**
     * Verify the fuzzy flag is passed on.
     */
    public function test_it_passes_the_fuzzy_flag(): void
    {
        $this->mock(HighlightService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')->once()->with('headphnes', true)->andReturn([
                'total' => 0,
                'markers' => ['start' => '[[hl]]', 'end' => '[[/hl]]'],
                'hits' => [],
            ]);
        });

        $this->getJson('/api/v1/highlight?q=headphnes&fuzzy=1')->assertOk();
    }

    /**
     * Verify the query is required.
     */
    public function test_it_requires_a_query(): void
    {
        $this->getJson('/api/v1/highlight')->assertUnprocessable()->assertJsonValidationErrors('q');
    }
}
