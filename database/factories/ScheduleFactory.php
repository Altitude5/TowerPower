<?php

namespace Database\Factories;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shop_id' => \App\Models\Shop::factory(),
            'city_id' => \App\Models\City::factory(),
            'delivery_person_id' => \App\Models\User::factory(),
            'type' => $this->faker->randomElement(['positive', 'negative']),
            'recurrence' => $this->faker->randomElement([
                'one_time', 
                'daily', 
                'weekdays_sunday', 
                'weekdays_monday', 
                'weekly_single_day', 
                'weekend_friday', 
                'weekend_saturday'
            ]),
            'day_of_week' => $this->faker->numberBetween(0, 6),
            'date' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
