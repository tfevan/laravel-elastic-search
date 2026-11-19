<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class StoreSeeder extends Seeder
{
    private const AREAS = [
        'Gulshan' => [23.7925, 90.4078],
        'Banani' => [23.7937, 90.4066],
        'Dhanmondi' => [23.7465, 90.3760],
        'Uttara' => [23.8759, 90.3795],
        'Mirpur' => [23.8223, 90.3654],
        'Mohammadpur' => [23.7662, 90.3589],
        'Motijheel' => [23.7330, 90.4172],
        'Old Dhaka' => [23.7104, 90.4074],
        'Bashundhara' => [23.8191, 90.4526],
        'Badda' => [23.7806, 90.4258],
        'Farmgate' => [23.7576, 90.3894],
        'Tejgaon' => [23.7640, 90.3910],
        'Malibagh' => [23.7487, 90.4150],
        'Khilgaon' => [23.7507, 90.4312],
        'Jatrabari' => [23.7100, 90.4350],
    ];

    private const TYPES = ['Electronics', 'Grocery', 'Pharmacy', 'Bookshop', 'Cafe'];

    private const OFFSETS = [[0.0030, -0.0020], [-0.0025, 0.0035], [0.0010, 0.0045]];

    /**
     * Seed stores around Dhaka and bulk import them into Elasticsearch.
     */
    public function run(): void
    {
        Store::withoutSyncingToSearch(function (): void {
            $index = 0;

            foreach (self::AREAS as $area => [$latitude, $longitude]) {
                foreach (self::OFFSETS as [$latitudeOffset, $longitudeOffset]) {
                    $type = self::TYPES[$index % count(self::TYPES)];

                    Store::query()->updateOrCreate(
                        ['name' => "{$area} {$type}"],
                        [
                            'type' => $type,
                            'area' => $area,
                            'city' => 'Dhaka',
                            'latitude' => $latitude + $latitudeOffset,
                            'longitude' => $longitude + $longitudeOffset,
                        ],
                    );

                    $index++;
                }
            }
        });

        Artisan::call('elastic:migrate');
        Artisan::call('scout:import', ['model' => Store::class]);
    }
}
