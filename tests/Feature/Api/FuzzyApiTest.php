<?php

namespace Tests\Feature\Api;

use App\Data\FuzzyOptions;
use App\Services\Search\FuzzyService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class FuzzyApiTest extends TestCase
{
    /**
     * Verify the request is mapped to fuzzy options and the result is returned.
     */
    public function test_it_passes_the_options_to_the_service(): void
    {
        $this->mock(FuzzyService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')
                ->once()
                ->with(Mockery::on(fn (FuzzyOptions $options): bool => $options->query === 'headphnes'
                    && $options->fuzziness === '2'
                    && $options->prefixLength === 1))
                ->andReturn([
                    'total' => 0,
                    'hits' => [],
                    'counts' => ['0' => 0, '1' => 0, '2' => 0, 'AUTO' => 0],
                    'suggestion' => 'headphones',
                ]);
        });

        $this->getJson('/api/v1/fuzzy?q=headphnes&fuzziness=2&prefix_length=1')
            ->assertOk()
            ->assertJsonPath('data.suggestion', 'headphones');
    }

    /**
     * Verify the defaults are AUTO fuzziness and no exact prefix.
     */
    public function test_it_uses_default_options(): void
    {
        $this->mock(FuzzyService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')
                ->once()
                ->with(Mockery::on(fn (FuzzyOptions $options): bool => $options->fuzziness === 'AUTO' && $options->prefixLength === 0))
                ->andReturn(['total' => 0, 'hits' => [], 'counts' => [], 'suggestion' => null]);
        });

        $this->getJson('/api/v1/fuzzy?q=phone')->assertOk();
    }

    /**
     * Verify invalid input is rejected.
     */
    public function test_it_validates_the_input(): void
    {
        $this->getJson('/api/v1/fuzzy?fuzziness=3&prefix_length=9')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['q', 'fuzziness', 'prefix_length']);
    }
}
