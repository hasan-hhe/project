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
        // Run all seeders needed to fully populate the database
        $this->call([
            UserSeeder::class,
            GovernorateSeeder::class,
            CitySeeder::class,
            ApartmentSeeder::class,
            BookingSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
