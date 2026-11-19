<?php

namespace Tests\Integration;

use App\Models\Product;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\ScoutDriverPlus\Support\Query;

class ProductIndexingTest extends ElasticsearchTestCase
{
    /**
     * Verify the index rejects fields that are not in the mapping.
     */
    public function test_the_mapping_is_strict(): void
    {
        $mapping = $this->client()->indices()->getMapping(['index' => 'test_products'])->asArray();

        $this->assertSame('strict', $mapping['test_products']['mappings']['dynamic']);

        $this->expectException(ClientResponseException::class);

        $this->client()->index(['index' => 'test_products', 'id' => '999', 'body' => ['unknown_field' => 'x']]);
    }

    /**
     * Verify a saved product becomes searchable.
     */
    public function test_a_saved_product_is_searchable(): void
    {
        $this->createProduct(['name' => 'Wireless Mouse']);

        $result = Product::searchQuery(Query::match()->field('name')->query('mouse'))->execute();

        $this->assertCount(1, $result->models());
    }

    /**
     * Verify inactive products are never indexed.
     */
    public function test_an_inactive_product_is_not_indexed(): void
    {
        $this->createProduct(['name' => 'Hidden Mouse', 'is_active' => false]);

        $result = Product::searchQuery(Query::match()->field('name')->query('mouse'))->execute();

        $this->assertCount(0, $result->models());
    }

    /**
     * Verify deleting a product removes it from the index.
     */
    public function test_a_deleted_product_leaves_the_index(): void
    {
        $product = $this->createProduct(['name' => 'Gaming Mouse']);
        $product->delete();
        $this->refreshIndices();

        $result = Product::searchQuery(Query::match()->field('name')->query('mouse'))->execute();

        $this->assertCount(0, $result->models());
    }
}
