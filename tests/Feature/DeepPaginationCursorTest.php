<?php

namespace Tests\Feature;

use App\Exceptions\InvalidCursorException;
use App\Services\Search\DeepPaginationService;
use Tests\TestCase;

class DeepPaginationCursorTest extends TestCase
{
    /**
     * Verify a cursor survives a round trip.
     */
    public function test_a_cursor_round_trips(): void
    {
        $service = app(DeepPaginationService::class);
        $state = ['pit' => 'abc123', 'after' => [49.99, 1048576], 'sort' => 'price_asc'];

        $this->assertSame($state, $service->decode($service->encode($state)));
    }

    /**
     * Verify a tampered cursor is rejected.
     */
    public function test_a_tampered_cursor_is_rejected(): void
    {
        $this->expectException(InvalidCursorException::class);

        app(DeepPaginationService::class)->decode('not-a-real-cursor');
    }

    /**
     * Verify a cursor with an unknown sort is rejected.
     */
    public function test_a_cursor_with_an_unknown_sort_is_rejected(): void
    {
        $service = app(DeepPaginationService::class);

        $this->expectException(InvalidCursorException::class);

        $service->decode($service->encode(['pit' => 'abc', 'after' => [1], 'sort' => 'password']));
    }
}
