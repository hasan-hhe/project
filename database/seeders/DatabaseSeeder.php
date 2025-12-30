<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            GovernorateSeeder::class,
            CitySeeder::class,
            UserSeeder::class,
            ApartmentSeeder::class,
            BookingSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
