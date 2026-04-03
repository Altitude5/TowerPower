<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'owner_id' => null,
            'minimum_order' => fake()->optional()->numberBetween(10, 500),
        ];
    }

    /**
     * State: shop with an owner.
     */
    public function withOwner(?User $user = null): static
    {
        return $this->state(fn (): array => [
            'owner_id' => $user?->id ?? User::factory(),
        ]);
    }

    /**
     * State: shop with a minimum order value.
     */
    public function withMinimumOrder(int $amount = 50): static
    {
        return $this->state(fn (): array => [
            'minimum_order' => $amount,
        ]);
    }
}
