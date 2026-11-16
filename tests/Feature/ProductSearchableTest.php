<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;

class ProductSearchableTest extends TestCase
{
    /**
     * Verify new products are searchable by default.
     */
    public function test_new_products_are_searchable_by_default(): void
    {
        $this->assertTrue((new Product)->shouldBeSearchable());
    }

    /**
     * Verify inactive products are kept out of the index.
     */
    public function test_inactive_products_are_not_searchable(): void
    {
        $product = new Product(['is_active' => false]);

        $this->assertFalse($product->shouldBeSearchable());
    }
}
