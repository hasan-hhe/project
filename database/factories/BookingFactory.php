<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Apartment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+30 days');
        $endDate = fake()->dateTimeBetween($startDate, '+60 days');
        $days = (int)((clone $endDate)->diff((clone $startDate))->days) + 1;
        $pricePerDay = fake()->randomFloat(2, 50, 500);
        $totalPrice = $days * $pricePerDay;

        return [
            'renter_id' => User::factory(),
            'apartment_id' => Apartment::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_price' => $totalPrice,
            'cancel_reason' => null,
            'status' => fake()->randomElement(['PENDING', 'CONFIRMED', 'CANCLED', 'COMPLETED']),
        ];
    }
}

