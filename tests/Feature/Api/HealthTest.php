<?php

namespace Tests\Feature\Api;

use App\Services\Elasticsearch\ClusterHealthService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Mockery\MockInterface;
use Tests\TestCase;

class HealthTest extends TestCase
{
    /**
     * Verify the endpoint returns the cluster report.
     */
    public function test_it_returns_the_cluster_report(): void
    {
        $this->mock(ClusterHealthService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('report')->once()->andReturn([
                'cluster' => ['name' => 'docker-cluster', 'status' => 'green', 'nodes' => 1],
                'version' => '9.0.0',
                'indices' => [],
            ]);
        });

        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('data.cluster.status', 'green')
            ->assertJsonPath('data.version', '9.0.0');
    }

    /**
     * Verify the endpoint answers 503 when Elasticsearch is down.
     */
    public function test_it_returns_service_unavailable_when_elasticsearch_is_down(): void
    {
        $this->mock(ClusterHealthService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('report')->once()->andThrow(new NoNodeAvailableException('No node available'));
        });

        $this->getJson('/api/v1/health')
            ->assertStatus(503)
            ->assertJsonPath('message', 'Elasticsearch is unreachable.');
    }
}
