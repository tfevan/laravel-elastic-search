<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PaginationRequest extends FormRequest
{
    private const MAX_RESULT_WINDOW = 10000;

    /**
     * Get the validation rules for a paginated search.
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:10,20,50'],
        ];
    }

    /**
     * Get the checks that run after the basic rules pass.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->integer('page', 1) * $this->integer('per_page', 10) > self::MAX_RESULT_WINDOW) {
                    $validator->errors()->add(
                        'page',
                        'Elasticsearch cannot page beyond the first 10,000 results. Use search_after instead (chapter 14).',
                    );
                }
            },
        ];
    }
}
