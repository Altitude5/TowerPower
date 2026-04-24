<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word;

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.$this->faker->randomNumber(3)),
            'price' => $this->faker->numberBetween(100, 10000),
            'price_type' => $this->faker->randomElement(['Unit', 'Weight', 'Volume']),
            'shop_id' => Shop::factory(),
            'category_id' => Category::factory(),
            'available' => true,
        ];
    }

    public function withImage(string $path = 'products/example.png'): static
    {
        return $this->state(fn (array $attributes) => [
            'image_path' => $path,
        ]);
    }

    public function withStockQuantity(float $quantity = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => $quantity,
        ]);
    }

    public function withStockWeight(float $weight = 10.5): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_weight' => $weight,
        ]);
    }

    public function withStockVolume(float $volume = 5.0): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_volume' => $volume,
        ]);
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'available' => false,
        ]);
    }
}
