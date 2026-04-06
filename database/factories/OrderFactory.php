<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Tower;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tower_id' => Tower::factory(),
            'status' => OrderStatus::Pending,
        ];
    }
}
