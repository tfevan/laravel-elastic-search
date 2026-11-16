<?php

namespace App\Data;

readonly class ProductPage
{
    /**
     * Create a new page of search results.
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
    ) {}
}
