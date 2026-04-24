<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Street;
use App\Models\Tower;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tower>
 */
class TowerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $city = City::factory()->create();
        $street = Street::factory()->create(['city_id' => $city->id]);

        return [
            'name' => 'Tower '.$this->faker->unique()->word,
            'street_id' => $street->id,
            'city_id' => $city->id,
            'house_number' => $this->faker->buildingNumber,
            'zipcode' => $this->faker->postcode,
        ];
    }
}
