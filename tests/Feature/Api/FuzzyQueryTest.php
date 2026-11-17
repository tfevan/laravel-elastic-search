<?php

namespace Tests\Feature;

use App\Services\Search\FuzzyService;
use Tests\TestCase;

class FuzzyQueryTest extends TestCase
{
    /**
     * Verify the query carries the fuzziness and the prefix length.
     */
    public function test_it_builds_a_fuzzy_multi_match_query(): void
    {
        $json = json_encode(
            app(FuzzyService::class)->query('headphnes', 'AUTO', 2)->buildQuery(),
            JSON_THROW_ON_ERROR,
        );

        $this->assertStringContainsString('multi_match', $json);
        $this->assertStringContainsString('fuzziness', $json);
        $this->assertStringContainsString('AUTO', $json);
        $this->assertStringContainsString('prefix_length', $json);
        $this->assertStringContainsString('name^3', $json);
    }
}
