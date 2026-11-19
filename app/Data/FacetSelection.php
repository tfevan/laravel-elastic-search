<?php

namespace App\Data;

readonly class FacetSelection
{
    /**
     * Create a new set of selected facets.
     */
    public function __construct(
        public string $query = '',
        public array $categories = [],
        public array $brands = [],
        public ?string $priceRange = null,
        public ?float $minRating = null,
    ) {}
}
