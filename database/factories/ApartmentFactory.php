<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Apartment>
 */
class ApartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return[
            'owner_id' => User::factory(),
            'governorate_id' => Governorate::factory(),
            'city_id' => City::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 50, 500),
            'rooms_count' => $this->faker->numberBetween(1, 5),
            'address_line' => $this->faker->address(),
            'rating_avg' => $this->faker->randomFloat(2, 1, 5),
            'is_active' => $this->faker->boolean(),
            'is_recommended' => $this->faker->boolean(),
            'photosURL' => json_encode([$this->faker->imageUrl(), $this->faker->imageUrl()])
        ];
    }
}
