<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'price' => fake()->numberBetween(100, 50000),
            'description' => fake()->paragraph(),
            'stock' => fake()->numberBetween(0, 100),
            'category' => fake()->randomElement(Product::categories()),
            'image_path' => null,
        ];
    }
}
