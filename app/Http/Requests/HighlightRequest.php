<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HighlightRequest extends FormRequest
{
    /**
     * Get the validation rules for a highlighted search.
     */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'max:100'],
            'fuzzy' => ['nullable', 'boolean'],
        ];
    }
}
