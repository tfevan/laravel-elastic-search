<?php

namespace App\Http\Requests;

use App\Data\FuzzyOptions;
use Illuminate\Foundation\Http\FormRequest;

class FuzzyRequest extends FormRequest
{
    /**
     * Get the validation rules for a fuzzy search.
     */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'max:100'],
            'fuzziness' => ['nullable', 'in:0,1,2,AUTO'],
            'prefix_length' => ['nullable', 'integer', 'between:0,5'],
        ];
    }

    /**
     * Convert the validated input into fuzzy search options.
     */
    public function options(): FuzzyOptions
    {
        return new FuzzyOptions(
            query: $this->string('q')->toString(),
            fuzziness: $this->string('fuzziness', 'AUTO')->toString(),
            prefixLength: $this->integer('prefix_length'),
        );
    }
}
