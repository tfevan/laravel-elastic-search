<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use Searchable;

    protected $fillable = [
        'name',
        'description',
        'category',
        'brand',
        'price',
        'rating',
        'tags',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the attribute casts.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'rating' => 'decimal:1',
            'tags' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the data indexed in Elasticsearch.
     */
    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'brand' => $this->brand,
            'price' => (float) $this->price,
            'rating' => (float) $this->rating,
            'tags' => $this->tags,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * Determine whether the product belongs in the search index.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->is_active;
    }
}
