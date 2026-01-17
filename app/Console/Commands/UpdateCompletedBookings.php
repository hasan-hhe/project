<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateCompletedBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:update-completed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تحديث الحجوزات المؤكدة إلى مكتملة بعد انتهاء تاريخها';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        // البحث عن الحجوزات المؤكدة التي انتهى تاريخها
        $bookings = Booking::where('status', 'CONFIRMED')
            ->where('end_date', '<', $today)
            ->get();

        $count = $bookings->count();

        if ($count === 0) {
            $this->info('لا توجد حجوزات مؤكدة منتهية التاريخ.');
            return Command::SUCCESS;
        }

        // تحديث الحالة إلى COMPLETED
        $updated = Booking::where('status', 'CONFIRMED')
            ->where('end_date', '<', $today)
            ->update(['status' => 'COMPLETED']);

        $this->info("تم تحديث {$updated} حجز من CONFIRMED إلى COMPLETED.");

        return Command::SUCCESS;
    }
}
