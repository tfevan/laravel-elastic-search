<?php

namespace App\Http\Requests;

use App\Data\RelevanceOptions;
use Illuminate\Foundation\Http\FormRequest;

class RelevanceRequest extends FormRequest
{
    /**
     * Get the validation rules for a relevance request.
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:200'],
            'name_boost' => ['nullable', 'numeric', 'between:0.1,10'],
            'description_boost' => ['nullable', 'numeric', 'between:0.1,10'],
            'operator' => ['nullable', 'in:or,and'],
            'prefer_top_rated' => ['nullable', 'boolean'],
            'prefer_popular' => ['nullable', 'boolean'],
            'category' => ['nullable', 'string', 'max:100'],
            'exclude_brand' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Convert the validated input into relevance options.
     */
    public function options(): RelevanceOptions
    {
        return new RelevanceOptions(
            query: $this->string('q')->toString(),
            nameBoost: (float) $this->input('name_boost', 3),
            descriptionBoost: (float) $this->input('description_boost', 1),
            operator: $this->string('operator', 'or')->toString(),
            preferTopRated: $this->boolean('prefer_top_rated'),
            preferPopular: $this->boolean('prefer_popular'),
            category: $this->filled('category')
                ? $this->string('category')->toString()
                : null,
            excludeBrand: $this->filled('exclude_brand')
                ? $this->string('exclude_brand')->toString()
                : null,
            minPrice: $this->filled('min_price')
                ? (float) $this->input('min_price')
                : null,
            maxPrice: $this->filled('max_price')
                ? (float) $this->input('max_price')
                : null,
        );
    }
}
