<?php

namespace Tests\Feature;

use App\Services\Search\HighlightService;
use Tests\TestCase;

class HighlightQueryTest extends TestCase
{
    /**
     * Verify fuzziness is only added when requested.
     */
    public function test_it_adds_fuzziness_only_when_requested(): void
    {
        $service = app(HighlightService::class);

        $strict = json_encode($service->query('phone', false)->buildQuery(), JSON_THROW_ON_ERROR);
        $fuzzy = json_encode($service->query('phone', true)->buildQuery(), JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString('fuzziness', $strict);
        $this->assertStringContainsString('fuzziness', $fuzzy);
    }
}
