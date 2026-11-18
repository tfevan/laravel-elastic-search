<?php

namespace Tests\Feature;

use App\Services\Search\AutocompleteService;
use Tests\TestCase;

class AutocompleteQueryTest extends TestCase
{
    /**
     * Verify every typed word must match the n-gram sub-field.
     */
    public function test_it_queries_the_autocomplete_sub_field_with_the_and_operator(): void
    {
        $json = json_encode(app(AutocompleteService::class)->query('wire hea')->buildQuery(), JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('name.autocomplete', $json);
        $this->assertStringContainsString('wire hea', $json);
        $this->assertStringContainsString('"and"', $json);
    }
}
