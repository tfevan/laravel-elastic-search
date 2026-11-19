<?php

namespace Tests\Feature\Api;

use App\Exceptions\AliasOperationException;
use App\Services\Elasticsearch\AliasReindexService;
use Mockery\MockInterface;
use Tests\TestCase;

class CatalogAliasApiTest extends TestCase
{
    /**
     * Verify the alias status is returned.
     */
    public function test_it_returns_the_alias_status(): void
    {
        $this->mock(AliasReindexService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('status')->once()->andReturn([
                'alias' => 'catalog',
                'index' => 'catalog_v2',
                'documents' => 1000,
                'indices' => [],
            ]);
        });

        $this->getJson('/api/v1/performance/catalog')
            ->assertOk()
            ->assertJsonPath('data.index', 'catalog_v2');
    }

    /**
     * Verify a reindex returns the steps that were taken.
     */
    public function test_it_reindexes(): void
    {
        $this->mock(AliasReindexService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('reindex')->once()->andReturn([
                'from' => 'catalog_v1',
                'to' => 'catalog_v2',
                'copied' => 1000,
                'steps' => [['step' => 'Switch the alias', 'ms' => 4.2]],
                'status' => [],
            ]);
        });

        $this->postJson('/api/v1/performance/catalog/reindex')
            ->assertOk()
            ->assertJsonPath('data.to', 'catalog_v2');
    }

    /**
     * Verify a rollback without an earlier version answers 409.
     */
    public function test_it_answers_conflict_when_there_is_nothing_to_roll_back_to(): void
    {
        $this->mock(AliasReindexService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('rollback')->once()->andThrow(new AliasOperationException('There is no previous version to roll back to.'));
        });

        $this->postJson('/api/v1/performance/catalog/rollback')
            ->assertStatus(409)
            ->assertJsonPath('message', 'There is no previous version to roll back to.');
    }
}
