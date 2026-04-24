<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
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
            'user_id' => User::factory(),
            'amount' => $this->faker->numberBetween(100, 10000),
            'status' => TransactionStatus::Pending,
            'transaction_reference' => $this->faker->optional()->uuid,
            'currency' => 'ILS',
            'gateway' => config('payments.default_gateway', 'stripe'),
        ];
    }
}
