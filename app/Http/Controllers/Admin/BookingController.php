<?php

namespace App\Http\Controllers\Admin;

use App\Models\Booking;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['renter', 'apartment.owner'])->orderBy('id', 'DESC');

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by renter
        if ($request->has('renter_id') && !empty($request->renter_id)) {
            $query->where('renter_id', $request->renter_id);
        }

        // Filter by apartment
        if ($request->has('apartment_id') && !empty($request->apartment_id)) {
            $query->where('apartment_id', $request->apartment_id);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('renter', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })
                    ->orWhereHas('apartment', function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $bookings = $query->paginate(defined('paginateNumber') ? constant('paginateNumber') : 10)
            ->withQueryString();

        $renters = User::where('account_type', 'RENTER')->get();
        $apartments = Apartment::all();

        return view('admin.bookings.index', compact('bookings', 'renters', 'apartments'));
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking)
    {
        $booking->load(['renter', 'apartment.owner', 'reviews.user']);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:PENDING,CONFIRMED,CANCLED,COMPLETED',
            'cancel_reason' => 'required_if:status,CANCLED|string|max:255',
        ]);

        try {
            $booking->update([
                'status' => $request->status,
                'cancel_reason' => $request->cancel_reason ?? $booking->cancel_reason,
            ]);

            $statusLabels = [
                'PENDING' => 'قيد الانتظار',
                'CONFIRMED' => 'مؤكدة',
                'CANCLED' => 'ملغاة',
                'COMPLETED' => 'مكتملة',
            ];

            return redirect()
                ->back()
                ->with('success', __('تم تغيير حالة الحجز إلى: ' . $statusLabels[$request->status]));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء تحديث الحالة'));
        }
    }

    /**
     * Remove the specified booking from storage.
     */
    public function destroy(Booking $booking)
    {
        try {
            DB::beginTransaction();
            $booking->delete();
            DB::commit();

            return redirect()
                ->back()
                ->with('success', __('تم حذف الحجز بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', __('حدث خطأ أثناء حذف الحجز: ') . $e->getMessage());
        }
    }
}
