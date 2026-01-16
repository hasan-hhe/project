<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Review;
use App\Http\Controllers\Controller;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $lastMonth = Carbon::now()->subMonth();
        $lastWeek = Carbon::now()->subWeek();

        // Users stats
        $totalUsers = User::count();
        $rentersCount = User::where('account_type', 'RENTER')->count();
        $ownersCount = User::where('account_type', 'OWNER')->count();
        $adminsCount = User::where('account_type', 'ADMIN')->count();
        $newUsersLastMonth = User::where('created_at', '>=', $lastMonth)->count();
        $newUsersLastWeek = User::where('created_at', '>=', $lastWeek)->count();

        // Apartments
        $totalApartments = Apartment::count();
        $activeApartments = Apartment::where('is_active', true)->count();
        $inactiveApartments = Apartment::where('is_active', false)->count();
        $newApartmentsLastMonth = Apartment::where('created_at', '>=', $lastMonth)->count();
        $newApartmentsLastWeek = Apartment::where('created_at', '>=', $lastWeek)->count();

        // Bookings
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'PENDING')->count();
        $confirmedBookings = Booking::where('status', 'CONFIRMED')->count();
        $cancelledBookings = Booking::where('status', 'CANCLED')->count();
        $completedBookings = Booking::where('status', 'COMPLETED')->count();
        $newBookingsLastMonth = Booking::where('created_at', '>=', $lastMonth)->count();
        $newBookingsLastWeek = Booking::where('created_at', '>=', $lastWeek)->count();

        // Revenue
        $totalRevenue = Booking::where('status', 'COMPLETED')->sum('total_price');
        $revenueLastMonth = Booking::where('status', 'COMPLETED')
            ->where('created_at', '>=', $lastMonth)
            ->sum('total_price');
        $revenueLastWeek = Booking::where('status', 'COMPLETED')
            ->where('created_at', '>=', $lastWeek)
            ->sum('total_price');

        // Reviews
        $totalReviews = Review::count();
        $averageRating = Apartment::avg('rating_avg') ?? 0;
        $newReviewsLastMonth = Review::where('created_at', '>=', $lastMonth)->count();

        // Owners status
        $pendingOwners = User::where('account_type', 'OWNER')
            ->where('status', 'PENDING')
            ->count();
        $approvedOwners = User::where('account_type', 'OWNER')
            ->where('status', 'APPROVED')
            ->count();
        $rejectedOwners = User::where('account_type', 'OWNER')
            ->where('status', 'REJECTED')
            ->count();

        // Weekly chart data
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::SATURDAY);
        $endOfWeek = $startOfWeek->copy()->addDays(6);

        $bookingsThisWeek = Booking::whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get();

        $chartLabels = [];
        $dailyBookings = [];
        $dailyRevenue = [];

        $period = CarbonPeriod::create($startOfWeek, $endOfWeek);
        foreach ($period as $date) {
            $day = $date->format('Y-m-d');
            $chartLabels[] = $date->translatedFormat('D d/m');
            $dailyBookings[$day] = 0;
            $dailyRevenue[$day] = 0;
        }

        foreach ($bookingsThisWeek as $booking) {
            $date = Carbon::parse($booking->created_at)->format('Y-m-d');
            if (isset($dailyBookings[$date])) {
                $dailyBookings[$date]++;
                if ($booking->status == 'COMPLETED') {
                    $dailyRevenue[$date] += $booking->total_price;
                }
            }
        }

        $chartBookingsData = array_values($dailyBookings);
        $chartRevenueData = array_values($dailyRevenue);

        if (empty($chartLabels)) {
            $chartLabels = ['لا توجد بيانات'];
            $chartBookingsData = [0];
            $chartRevenueData = [0];
        }

        // Monthly data
        $monthsLabels = [];
        $usersMonthlyData = [];
        $apartmentsMonthlyData = [];
        $bookingsMonthlyData = [];
        $revenueMonthlyData = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthsLabels[] = Carbon::now()->subMonths($i)->translatedFormat('M Y');

            $usersMonthlyData[] = User::whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2))
                ->count();

            $apartmentsMonthlyData[] = Apartment::whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2))
                ->count();

            $bookingsMonthlyData[] = Booking::whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2))
                ->count();

            $revenueMonthlyData[] = Booking::where('status', 'COMPLETED')
                ->whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2))
                ->sum('total_price');
        }

        if (empty($monthsLabels)) {
            $monthsLabels = ['لا توجد بيانات'];
            $usersMonthlyData = [0];
            $apartmentsMonthlyData = [0];
            $bookingsMonthlyData = [0];
            $revenueMonthlyData = [0];
        }

        // Top rated
        $topRatedApartments = Apartment::orderBy('rating_avg', 'DESC')
            ->where('rating_avg', '>', 0)
            ->limit(5)
            ->get(['id', 'title', 'rating_avg', 'price']);

        // Upcoming bookings
        $upcomingBookings = Booking::where('status', 'CONFIRMED')
            ->whereBetween('start_date', [Carbon::now(), Carbon::now()->addWeek()])
            ->with(['apartment', 'renter'])
            ->orderBy('start_date', 'ASC')
            ->limit(10)
            ->get();

        return view('admin.dashboard.index', compact(
            'totalUsers',
            'rentersCount',
            'ownersCount',
            'adminsCount',
            'newUsersLastMonth',
            'newUsersLastWeek',
            'totalApartments',
            'activeApartments',
            'inactiveApartments',
            'newApartmentsLastMonth',
            'newApartmentsLastWeek',
            'totalBookings',
            'pendingBookings',
            'confirmedBookings',
            'cancelledBookings',
            'completedBookings',
            'newBookingsLastMonth',
            'newBookingsLastWeek',
            'totalRevenue',
            'revenueLastMonth',
            'revenueLastWeek',
            'totalReviews',
            'averageRating',
            'newReviewsLastMonth',
            'pendingOwners',
            'approvedOwners',
            'rejectedOwners',
            'chartLabels',
            'chartBookingsData',
            'chartRevenueData',
            'monthsLabels',
            'usersMonthlyData',
            'apartmentsMonthlyData',
            'bookingsMonthlyData',
            'revenueMonthlyData',
            'topRatedApartments',
            'upcomingBookings'
        ));
    }
}
