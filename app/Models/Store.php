<?php

namespace App\Models;

use Elastic\ScoutDriverPlus\Searchable;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use Searchable;

    protected $fillable = [
        'name',
        'type',
        'area',
        'city',
        'latitude',
        'longitude',
    ];

    /**
     * Get the attribute casts.
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Get the data indexed in Elasticsearch.
     */
    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'area' => $this->area,
            'city' => $this->city,
            'location' => ['lat' => $this->latitude, 'lon' => $this->longitude],
        ];
    }
}
