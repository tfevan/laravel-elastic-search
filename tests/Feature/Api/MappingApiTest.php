<?php

namespace Tests\Feature\Api;

use App\Exceptions\IndexNotFoundException;
use App\Services\Elasticsearch\MappingService;
use Mockery\MockInterface;
use Tests\TestCase;

class MappingApiTest extends TestCase
{
    /**
     * Verify the mapping description is returned.
     */
    public function test_it_describes_the_index(): void
    {
        $this->mock(MappingService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('describe')->once()->andReturn([
                'mapping' => ['properties' => ['name' => ['type' => 'text']]],
                'settings' => ['shards' => 1, 'replicas' => 0, 'analyzers' => ['product_search'], 'filters' => []],
            ]);
        });

        $this->getJson('/api/v1/mapping')
            ->assertOk()
            ->assertJsonPath('data.mapping.properties.name.type', 'text')
            ->assertJsonPath('data.settings.replicas', 0);
    }

    /**
     * Verify a missing index answers 404 with a hint.
     */
    public function test_it_answers_not_found_when_the_index_is_missing(): void
    {
        $this->mock(MappingService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('describe')->once()->andThrow(new IndexNotFoundException('The products index does not exist.'));
        });

        $this->getJson('/api/v1/mapping')->assertNotFound();
    }

    /**
     * Verify the analyzer output is returned.
     */
    public function test_it_analyzes_a_text(): void
    {
        $this->mock(MappingService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('analyze')->once()->with('Mobile', 'product_search')->andReturn([
                ['token' => 'mobile', 'position' => 0, 'type' => 'SYNONYM'],
            ]);
        });

        $this->getJson('/api/v1/mapping/analyze?text=Mobile&analyzer=product_search')
            ->assertOk()
            ->assertJsonPath('data.tokens.0.token', 'mobile');
    }

    /**
     * Verify unknown analyzers and missing text are rejected.
     */
    public function test_it_validates_the_analyze_request(): void
    {
        $this->getJson('/api/v1/mapping/analyze?analyzer=unknown')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['text', 'analyzer']);
    }
}
