<?php

namespace App\Http\Requests;

use App\Data\FacetSelection;
use App\Services\Search\FacetService;
use Illuminate\Foundation\Http\FormRequest;

class FacetRequest extends FormRequest
{
    /**
     * Get the validation rules for a faceted search.
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'categories' => ['nullable', 'array', 'max:10'],
            'categories.*' => ['string', 'max:100'],
            'brands' => ['nullable', 'array', 'max:10'],
            'brands.*' => ['string', 'max:100'],
            'price_range' => ['nullable', 'in:'.implode(',', array_keys(FacetService::PRICE_RANGES))],
            'min_rating' => ['nullable', 'in:'.implode(',', array_keys(FacetService::RATINGS))],
        ];
    }

    /**
     * Convert the validated input into a facet selection.
     */
    public function selection(): FacetSelection
    {
        return new FacetSelection(
            query: $this->string('q')->toString(),
            categories: array_values((array) $this->input('categories', [])),
            brands: array_values((array) $this->input('brands', [])),
            priceRange: $this->filled('price_range') ? $this->string('price_range')->toString() : null,
            minRating: $this->filled('min_rating') ? (float) $this->input('min_rating') : null,
        );
    }
}
