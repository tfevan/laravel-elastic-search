<?php

declare(strict_types=1);

use Elastic\Adapter\Indices\Mapping;
use Elastic\Adapter\Indices\Settings;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

final class CreateProductsIndex implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Index::dropIfExists('products');

        Index::create('products', function (Mapping $mapping, Settings $settings): void {
            $mapping->dynamic('strict');

            $mapping->text('name', [
                'analyzer' => 'product_index',
                'search_analyzer' => 'product_search',
                'fields' => [
                    'keyword' => ['type' => 'keyword', 'ignore_above' => 256],
                ],
            ]);
            $mapping->text('description', [
                'analyzer' => 'product_index',
                'search_analyzer' => 'product_search',
            ]);
            $mapping->keyword('category');
            $mapping->keyword('brand');
            $mapping->keyword('tags');
            $mapping->scaledFloat('price', ['scaling_factor' => 100]);
            $mapping->float('rating');
            $mapping->date('created_at');

            $settings->index(['number_of_replicas' => 0]);
            $settings->analysis([
                'filter' => [
                    'product_synonyms' => [
                        'type' => 'synonym_graph',
                        'synonyms' => [
                            'phone, smartphone, mobile',
                            'laptop, notebook',
                            'headphones, earbuds, headset',
                            'speaker, soundbar',
                        ],
                    ],
                ],
                'analyzer' => [
                    'product_index' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase', 'asciifolding'],
                    ],
                    'product_search' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase', 'asciifolding', 'product_synonyms'],
                    ],
                ],
            ]);
        });
    }

    /**
     * Drop the products index.
     */
    public function down(): void
    {
        Index::dropIfExists('products');
    }
}
