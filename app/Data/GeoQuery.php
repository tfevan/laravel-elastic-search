<?php

namespace App\Data;

readonly class GeoQuery
{
    /**
     * Create a new nearby store query.
     */
    public function __construct(
        public float $latitude,
        public float $longitude,
        public float $radiusKm = 3.0,
        public ?string $type = null,
        public string $query = '',
    ) {}
}
