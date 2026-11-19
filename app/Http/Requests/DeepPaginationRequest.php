<?php

namespace App\Http\Requests;

use App\Services\Search\DeepPaginationService;
use Illuminate\Foundation\Http\FormRequest;

class DeepPaginationRequest extends FormRequest
{
    /**
     * Get the validation rules for a cursor paginated request.
     */
    public function rules(): array
    {
        return [
            'sort' => ['nullable', 'in:'.implode(',', array_keys(DeepPaginationService::SORTS))],
            'size' => ['nullable', 'in:10,20,50'],
            'cursor' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
