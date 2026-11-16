<?php

namespace App\Data;

readonly class RelevanceOptions
{
    /**
     * Create a new set of relevance options.
     */
    public function __construct(
        public string $query = '',
        public float $nameBoost = 3.0,
        public float $descriptionBoost = 1.0,
        public string $operator = 'or',
        public bool $preferTopRated = false,
        public bool $preferPopular = false,
        public ?string $category = null,
        public ?string $excludeBrand = null,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
    ) {}
}
