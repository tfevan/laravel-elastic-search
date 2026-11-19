<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TouchProductsRequest extends FormRequest
{
    /**
     * Get the validation rules for touching products.
     */
    public function rules(): array
    {
        return [
            'count' => ['required', 'in:10,50,200'],
        ];
    }
}
