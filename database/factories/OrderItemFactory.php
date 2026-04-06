<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\SubOrder;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sub_order_id' => SubOrder::factory(),
            'product_id' => Product::factory(),
            'product_name' => $this->faker->word,
            'price' => $this->faker->numberBetween(100, 10000),
            'price_type' => 'Unit',
            'quantity' => '1.000',
        ];
    }
}
