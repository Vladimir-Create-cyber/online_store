<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => Str::slug($this->faker->words(3, true)),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 100, 5000),
            'image' => $this->faker->imageUrl(400, 400, 'products'),
        ];
    }
}
