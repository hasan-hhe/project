<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Booking;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $completedBookings = Booking::where('status', 'COMPLETED')
            ->with(['renter', 'apartment'])
            ->get();

        if ($completedBookings->isEmpty()) {
            $this->command->warn('لا توجد حجوزات مكتملة. قم بتشغيل BookingSeeder أولاً.');
            return;
        }

        $comments = [
            'شقة رائعة ومريحة، أنصح بها بشدة',
            'تجربة ممتازة، المالك متعاون جداً',
            'الشقة نظيفة ومرتبة، موقع ممتاز',
            'خدمة جيدة، لكن السعر مرتفع قليلاً',
            'شقة جميلة مع إطلالة رائعة',
            'تجربة جيدة بشكل عام',
            'الشقة مناسبة للعائلات',
            'موقع ممتاز وخدمة جيدة',
            'شقة فاخرة مع جميع الخدمات',
            'تجربة رائعة، سأعود مرة أخرى',
        ];

        foreach ($completedBookings as $booking) {
            // إنشاء تقييم واحد لكل حجز مكتمل (70% من الحجوزات)
            if (rand(1, 100) <= 70) {
                Review::create([
                    'apartment_id' => $booking->apartment_id,
                    'user_id' => $booking->renter_id,
                    'booking_id' => $booking->id,
                    'rating' => rand(3, 5),
                    'comment' => $comments[array_rand($comments)],
                ]);

                // تحديث متوسط التقييم للشقة
                $apartment = $booking->apartment;
                $averageRating = Review::where('apartment_id', $apartment->id)->avg('rating');
                $apartment->update(['rating_avg' => round($averageRating, 2)]);
            }
        }
    }
}
