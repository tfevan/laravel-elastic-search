<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    private const CATALOG = [
        'Phones' => ['Smartphone', 'Feature Phone', 'Phone Case', 'Wireless Charger'],
        'Laptops' => ['Gaming Laptop', 'Ultrabook', 'Laptop Sleeve', 'Docking Station'],
        'Audio' => ['Wireless Headphones', 'Bluetooth Speaker', 'Earbuds', 'Soundbar'],
        'Cameras' => ['Mirrorless Camera', 'Action Camera', 'Camera Tripod', 'Lens Kit'],
        'Wearables' => ['Smartwatch', 'Fitness Tracker', 'Smart Ring'],
        'Home' => ['Robot Vacuum', 'Air Purifier', 'Smart Bulb', 'Coffee Maker'],
    ];

    private const BRANDS = ['Apex', 'Nova', 'Zenith', 'Orbit', 'Pulse', 'Vertex', 'Lumen', 'Atlas'];

    private const TAGS = ['new', 'popular', 'budget', 'premium', 'wireless', 'portable', 'eco', 'gaming', 'office', 'travel'];

    private const DESCRIPTIONS = [
        'Lightweight design with all-day battery life.',
        'Built with recycled materials for a lower footprint.',
        'Fast charging and a durable finish for daily use.',
        'Crystal clear performance in a compact body.',
        'Easy setup and seamless pairing with your devices.',
        'Water resistant and tested for extreme conditions.',
        'Premium build quality backed by a two year warranty.',
        'Smart features that adapt to the way you work.',
    ];

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $category = fake()->randomElement(array_keys(self::CATALOG));
        $type = fake()->randomElement(self::CATALOG[$category]);
        $brand = fake()->randomElement(self::BRANDS);

        return [
            'name' => sprintf('%s %s %s', $brand, $type, strtoupper(fake()->bothify('??-###'))),
            'description' => sprintf('%s by %s. %s', $type, $brand, implode(' ', fake()->randomElements(self::DESCRIPTIONS, 2))),
            'category' => $category,
            'brand' => $brand,
            'price' => fake()->randomFloat(2, 9, 1999),
            'rating' => fake()->randomFloat(1, 1, 5),
            'tags' => fake()->randomElements(self::TAGS, fake()->numberBetween(1, 3)),
        ];
    }
}
