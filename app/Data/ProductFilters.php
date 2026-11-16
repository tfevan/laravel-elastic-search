<?php

namespace App\Data;

readonly class ProductFilters
{
    /**
     * Create a new filter set.
     */
    public function __construct(
        public string $query = '',
        public ?string $category = null,
        public array $brands = [],
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public string $sort = 'relevance',
    ) {}

    /**
     * Build the filter set from validated request data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            query: $data['q'] ?? '',
            category: $data['category'] ?? null,
            brands: $data['brands'] ?? [],
            minPrice: isset($data['min_price']) ? (float) $data['min_price'] : null,
            maxPrice: isset($data['max_price']) ? (float) $data['max_price'] : null,
            sort: $data['sort'] ?? 'relevance',
        );
    }
}
