<?php

namespace Tests\Feature\Api;

use App\Services\Search\AutocompleteService;
use Mockery\MockInterface;
use Tests\TestCase;

class AutocompleteApiTest extends TestCase
{
    /**
     * Verify suggestions are returned for the typed text.
     */
    public function test_it_returns_suggestions(): void
    {
        $this->mock(AutocompleteService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('suggest')->once()->with('smart')->andReturn([
                ['id' => 1, 'name' => 'Apex Smartphone AB-123', 'category' => 'Phones', 'brand' => 'Apex'],
            ]);
        });

        $this->getJson('/api/v1/autocomplete?q=smart')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Apex Smartphone AB-123');
    }

    /**
     * Verify too short and too long input is rejected.
     */
    public function test_it_validates_the_length_of_the_text(): void
    {
        $this->getJson('/api/v1/autocomplete?q=s')->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson('/api/v1/autocomplete?q='.str_repeat('a', 51))->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson('/api/v1/autocomplete')->assertUnprocessable()->assertJsonValidationErrors('q');
    }
}
