<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Get the validation rules for a filter request.
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:100'],
            'brands' => ['nullable', 'array', 'max:10'],
            'brands.*' => ['string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => array_merge(['nullable', 'numeric', 'min:0'], $this->filled('min_price') ? ['gte:min_price'] : []),
            'sort' => ['nullable', 'in:relevance,price_asc,price_desc,rating_desc,name_asc'],
        ];
    }
}
