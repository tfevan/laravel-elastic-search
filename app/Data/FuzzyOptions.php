<?php

namespace App\Data;

readonly class FuzzyOptions
{
    /**
     * Create a new set of fuzzy search options.
     */
    public function __construct(
        public string $query,
        public string $fuzziness = 'AUTO',
        public int $prefixLength = 0,
    ) {}
}
