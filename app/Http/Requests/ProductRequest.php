<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Get the validation rules for creating and updating a product.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'rating' => ['required', 'numeric', 'between:1,5'],
            'tags' => ['array', 'max:10'],
            'tags.*' => ['string', 'max:30'],
        ];
    }
}
