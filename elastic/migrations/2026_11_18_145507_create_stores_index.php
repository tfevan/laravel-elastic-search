<?php

declare(strict_types=1);

use Elastic\Adapter\Indices\Mapping;
use Elastic\Adapter\Indices\Settings;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

final class CreateStoresIndex implements MigrationInterface
{
    /**
     * Create the stores index with a geo_point location.
     */
    public function up(): void
    {
        Index::dropIfExists('stores');

        Index::create('stores', function (Mapping $mapping, Settings $settings): void {
            $mapping->dynamic('strict');

            $mapping->text('name', [
                'fields' => [
                    'keyword' => ['type' => 'keyword', 'ignore_above' => 256],
                ],
            ]);
            $mapping->keyword('type');
            $mapping->keyword('area');
            $mapping->keyword('city');
            $mapping->geoPoint('location');

            $settings->index(['number_of_replicas' => 0]);
        });
    }

    /**
     * Drop the stores index.
     */
    public function down(): void
    {
        Index::dropIfExists('stores');
    }
}
