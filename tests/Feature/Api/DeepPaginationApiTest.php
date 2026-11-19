<?php

namespace Tests\Feature\Api;

use App\Exceptions\InvalidCursorException;
use App\Services\Search\DeepPaginationService;
use Mockery\MockInterface;
use Tests\TestCase;

class DeepPaginationApiTest extends TestCase
{
    /**
     * Verify the first page is requested without a cursor.
     */
    public function test_it_returns_the_first_page(): void
    {
        $this->mock(DeepPaginationService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('page')->once()->with('price_desc', 10, null)->andReturn([
                'items' => [],
                'cursor' => 'next-cursor',
                'took_ms' => 4,
            ]);
        });

        $this->getJson('/api/v1/performance/deep-pagination?sort=price_desc&size=10')
            ->assertOk()
            ->assertJsonPath('data.cursor', 'next-cursor');
    }

    /**
     * Verify the cursor is passed on to the service.
     */
    public function test_it_passes_the_cursor_on(): void
    {
        $this->mock(DeepPaginationService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('page')->once()->with('price_asc', 20, 'abc')->andReturn([
                'items' => [],
                'cursor' => null,
                'took_ms' => 1,
            ]);
        });

        $this->getJson('/api/v1/performance/deep-pagination?cursor=abc')->assertOk();
    }

    /**
     * Verify an invalid or expired cursor answers 422.
     */
    public function test_it_rejects_an_invalid_cursor(): void
    {
        $this->mock(DeepPaginationService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('page')->once()->andThrow(new InvalidCursorException('Invalid cursor.'));
        });

        $this->getJson('/api/v1/performance/deep-pagination?cursor=broken')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Invalid cursor.');
    }

    /**
     * Verify unsupported sorts and sizes are rejected.
     */
    public function test_it_validates_the_input(): void
    {
        $this->getJson('/api/v1/performance/deep-pagination?sort=random&size=7')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort', 'size']);
    }
}
