<?php

namespace App\Http\Requests;

use App\Data\GeoQuery;
use Illuminate\Foundation\Http\FormRequest;

class GeoRequest extends FormRequest
{
    /**
     * Get the validation rules for a nearby store search.
     */
    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'between:0.1,50'],
            'type' => ['nullable', 'string', 'max:50'],
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Convert the validated input into a geo query.
     */
    public function geoQuery(): GeoQuery
    {
        return new GeoQuery(
            latitude: (float) $this->input('latitude'),
            longitude: (float) $this->input('longitude'),
            radiusKm: (float) $this->input('radius_km', 3),
            type: $this->filled('type') ? $this->string('type')->toString() : null,
            query: $this->string('q')->toString(),
        );
    }
}
