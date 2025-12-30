<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apartments = Apartment::where('is_active', true)->get();
        $renters = User::where('account_type', 'RENTER')->get();

        if ($apartments->isEmpty() || $renters->isEmpty()) {
            $this->command->warn('لا توجد شقق نشطة أو مستأجرين. قم بتشغيل ApartmentSeeder و UserSeeder أولاً.');
            return;
        }

        $statuses = ['PENDING', 'CONFIRMED', 'COMPLETED', 'CANCLED'];

        // إنشاء حجوزات مكتملة
        for ($i = 0; $i < 15; $i++) {
            $apartment = $apartments->random();
            $renter = $renters->random();
            $startDate = Carbon::now()->subMonths(rand(1, 6))->subDays(rand(1, 30));
            $endDate = $startDate->copy()->addDays(rand(3, 14));
            $days = $startDate->diffInDays($endDate);
            $totalPrice = $apartment->price * $days;

            Booking::create([
                'renter_id' => $renter->id,
                'apartment_id' => $apartment->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_price' => $totalPrice,
                'status' => 'COMPLETED',
            ]);
        }

        // إنشاء حجوزات مؤكدة
        for ($i = 0; $i < 10; $i++) {
            $apartment = $apartments->random();
            $renter = $renters->random();
            $startDate = Carbon::now()->addDays(rand(1, 30));
            $endDate = $startDate->copy()->addDays(rand(3, 14));
            $days = $startDate->diffInDays($endDate);
            $totalPrice = $apartment->price * $days;

            Booking::create([
                'renter_id' => $renter->id,
                'apartment_id' => $apartment->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_price' => $totalPrice,
                'status' => 'CONFIRMED',
            ]);
        }

        // إنشاء حجوزات قيد الانتظار
        for ($i = 0; $i < 5; $i++) {
            $apartment = $apartments->random();
            $renter = $renters->random();
            $startDate = Carbon::now()->addDays(rand(1, 60));
            $endDate = $startDate->copy()->addDays(rand(3, 14));
            $days = $startDate->diffInDays($endDate);
            $totalPrice = $apartment->price * $days;

            Booking::create([
                'renter_id' => $renter->id,
                'apartment_id' => $apartment->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_price' => $totalPrice,
                'status' => 'PENDING',
            ]);
        }

        // إنشاء حجوزات ملغاة
        for ($i = 0; $i < 5; $i++) {
            $apartment = $apartments->random();
            $renter = $renters->random();
            $startDate = Carbon::now()->subDays(rand(1, 30));
            $endDate = $startDate->copy()->addDays(rand(3, 14));
            $days = $startDate->diffInDays($endDate);
            $totalPrice = $apartment->price * $days;

            Booking::create([
                'renter_id' => $renter->id,
                'apartment_id' => $apartment->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_price' => $totalPrice,
                'status' => 'CANCLED',
                'cancel_reason' => (['تغيير في الخطط', 'ظروف طارئة', 'وجدت شقة أفضل', 'مشكلة في الدفع'])[rand(0, 3)],
            ]);
        }
    }
}
