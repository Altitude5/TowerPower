<?php

namespace Database\Factories;

use App\Enums\SubOrderStatus;
use App\Models\Order;
use App\Models\Shop;
use App\Models\SubOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubOrder>
 */
class SubOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'shop_id' => Shop::factory(),
            'status' => SubOrderStatus::Pending,
        ];
    }
}
