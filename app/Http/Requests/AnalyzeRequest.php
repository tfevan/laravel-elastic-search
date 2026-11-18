<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeRequest extends FormRequest
{
    /**
     * Get the validation rules for an analyze request.
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:200'],
            'analyzer' => [
                'required',
                'in:standard,english,product_index,product_search,autocomplete_index,autocomplete_search',
            ],
        ];
    }
}
