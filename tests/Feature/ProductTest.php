<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify the searchable array exposes the indexed fields.
     */
    public function test_it_builds_the_searchable_array(): void
    {
        $product = Product::factory()->create();

        $this->assertSame(
            ['name', 'description', 'category', 'brand', 'price', 'rating', 'tags', 'created_at'],
            array_keys($product->toSearchableArray()),
        );
    }
}
