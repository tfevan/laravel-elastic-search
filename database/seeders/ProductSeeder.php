<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ProductSeeder extends Seeder
{
    private const TOTAL = 1000;

    /**
     * Seed products into the database and bulk import them into Elasticsearch.
     */
    public function run(): void
    {
        fake()->seed(2026);

        Product::withoutSyncingToSearch(function (): void {
            Product::factory()->count(self::TOTAL)->create();
        });

        Artisan::call('scout:import', ['model' => Product::class]);
    }
}
