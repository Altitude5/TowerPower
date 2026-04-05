<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'name' => fake()->unique()->words(3, true),
            'price' => fake()->numberBetween(100, 50000),
            'price_type' => fake()->randomElement(['Unit', 'Weight', 'Volume']),
            'image_path' => null,
            'sku' => null,
            'shop_id' => Shop::factory(),
            'stock_quantity' => null,
            'stock_weight' => null,
            'stock_volume' => null,
            'category_id' => null,
            'available' => true,
        ];
    }

    /**
     * State: product with an image.
     */
    public function withImage(string $path = 'products/sample.jpg'): static
    {
        return $this->state(fn (): array => [
            'image_path' => $path,
        ]);
    }

    /**
     * State: product with a SKU.
     */
    public function withSku(?string $sku = null): static
    {
        return $this->state(fn (): array => [
            'sku' => $sku ?? strtoupper(fake()->bothify('???-####-???')),
        ]);
    }

    /**
     * State: unavailable product.
     */
    public function unavailable(): static
    {
        return $this->state(fn (): array => [
            'available' => false,
        ]);
    }

    /**
     * State: product with stock quantity.
     */
    public function withStockQuantity(float $qty = 100.0): static
    {
        return $this->state(fn (): array => [
            'stock_quantity' => $qty,
            'stock_weight' => null,
            'stock_volume' => null,
        ]);
    }

    /**
     * State: product with stock weight.
     */
    public function withStockWeight(float $weight = 50.0): static
    {
        return $this->state(fn (): array => [
            'stock_quantity' => null,
            'stock_weight' => $weight,
            'stock_volume' => null,
        ]);
    }

    /**
     * State: product with stock volume.
     */
    public function withStockVolume(float $volume = 25.0): static
    {
        return $this->state(fn (): array => [
            'stock_quantity' => null,
            'stock_weight' => null,
            'stock_volume' => $volume,
        ]);
    }
}
