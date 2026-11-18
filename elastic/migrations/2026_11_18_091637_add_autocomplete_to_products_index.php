<?php

declare(strict_types=1);

use Elastic\Adapter\Indices\Mapping;
use Elastic\Adapter\Indices\Settings;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

final class AddAutocompleteToProductsIndex implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Index::pushSettings('products', function (Settings $settings): void {
            $settings->analysis([
                'filter' => [
                    'autocomplete_filter' => [
                        'type' => 'edge_ngram',
                        'min_gram' => 2,
                        'max_gram' => 15,
                    ],
                ],
                'analyzer' => [
                    'autocomplete_index' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase', 'asciifolding', 'autocomplete_filter'],
                    ],
                    'autocomplete_search' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase', 'asciifolding'],
                    ],
                ],
            ]);
        });

        Index::putMapping('products', function (Mapping $mapping): void {
            $mapping->text('name', [
                'analyzer' => 'product_index',
                'search_analyzer' => 'product_search',
                'fields' => [
                    'keyword' => ['type' => 'keyword', 'ignore_above' => 256],
                    'autocomplete' => [
                        'type' => 'text',
                        'analyzer' => 'autocomplete_index',
                        'search_analyzer' => 'autocomplete_search',
                    ],
                ],
            ]);
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        //
    }
}
