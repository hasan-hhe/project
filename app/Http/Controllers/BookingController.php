<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ReservationResource;
use App\Models\Booking;
use App\Models\Apartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use OpenApi\Attributes as OA;

class BookingController extends Controller
{
    #[OA\Get(path: "/reservations/my-reservations", tags: ["Reservations"], security: [["bearerAuth" => []]])]
    public function getMyReservations(Request $request)
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 10);
        $perPage = max(1, min(50, (int)$perPage));

        $bookings = Booking::where('renter_id', $user->id)
            ->with(['apartment', 'apartment.owner', 'apartment.photos'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success([
            'bookings' => ReservationResource::collection($bookings)
        ], 'تم جلب الحجوزات بنجاح.');
    }

    #[OA\Post(path: "/reservations", tags: ["Reservations"], security: [["bearerAuth" => []]])]
    public function store(Request $request)
    {
        $user = $request->user();

        try {
            $request->validate([
                'apartment_id' => 'required|exists:apartments,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after:start_date',
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        $apartment = Apartment::findOrFail($request->apartment_id);

        // check availability
        $conflictingBooking = Booking::where('apartment_id', $apartment->id)
            ->where('status', '!=', 'CANCLED')
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })
            ->exists();

        if ($conflictingBooking) {
            return ResponseHelper::error('الشقة غير متاحة للتواريخ المحددة.', 400);
        }

        // calc price
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $nights = $startDate->diffInDays($endDate);
        $totalPrice = $apartment->price * $nights;

        if ($user->wallet_balance < $totalPrice) {
            return ResponseHelper::error('رصيدك غير كافٍ.', 400);
        }

        try {
            DB::beginTransaction();

            $booking = Booking::create([
                'renter_id' => $user->id,
                'apartment_id' => $apartment->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_price' => $totalPrice,
                'status' => 'PENDING',
            ]);

            $booking->load(['apartment', 'apartment.owner']);

            DB::commit();

            return ResponseHelper::success([
                'booking' => new ReservationResource($booking)
            ], 'تم إنشاء الحجز بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في إنشاء الحجز: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(path: "/reservations/{id}/update", tags: ["Reservations"], security: [["bearerAuth" => []]])]
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $booking = Booking::where('id', $id)
            ->where('renter_id', $user->id)
            ->firstOrFail();

        if ($booking->status !== 'PENDING') {
            return ResponseHelper::error('يمكن تحديث الحجوزات المعلقة فقط.', 400);
        }

        try {
            $request->validate([
                'start_date' => 'nullable|date|after_or_equal:today',
                'end_date' => 'nullable|date|after:start_date',
                'change_reason' => 'nullable|string|max:500',
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        try {
            DB::beginTransaction();

            $startDate = $request->start_date ?? $booking->start_date;
            $endDate = $request->end_date ?? $booking->end_date;

            // check availability
            $conflictingBooking = Booking::where('apartment_id', $booking->apartment_id)
                ->where('id', '!=', $booking->id)
                ->where('status', '!=', 'CANCLED')
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                })
                ->exists();

            if ($conflictingBooking) {
                return ResponseHelper::error('الشقة غير متاحة للتواريخ المحددة.', 400);
            }

            // recalc price
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $nights = $start->diffInDays($end);
            $totalPrice = $booking->apartment->price * $nights;

            $updateData = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_price' => $totalPrice,
            ];

            if ($request->has('change_reason') && $request->change_reason) {
                $updateData['change_reason'] = $request->change_reason;
            }

            $booking->update($updateData);

            $booking->load(['apartment', 'apartment.owner']);

            DB::commit();

            return ResponseHelper::success([
                'booking' => new ReservationResource($booking)
            ], 'تم تحديث الحجز بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في تحديث الحجز: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(path: "/reservations/{id}/cancel", tags: ["Reservations"], security: [["bearerAuth" => []]])]
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('غير مصرح لك.', 401);
        }

        $booking = Booking::where('id', $id)
            ->where('renter_id', $user->id)
            ->firstOrFail();

        if ($booking->status !== 'PENDING') {
            return ResponseHelper::error('لا يمكن إلغاء إلا الحجوزات المعلقة.', 400);
        }

        try {
            $request->validate([
                'cancel_reason' => 'nullable|string|max:255',
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        try {
            DB::beginTransaction();

            $booking->update([
                'status' => 'CANCLED',
                'cancel_reason' => $request->cancel_reason ?? 'تم الإلغاء من قبل المستخدم',
            ]);

            DB::commit();

            return ResponseHelper::success(null, 'تم إلغاء الحجز بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في إلغاء الحجز: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(path: "/reservations/{id}/delete", tags: ["Reservations"], security: [["bearerAuth" => []]])]
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('غير مصرح لك.', 401);
        }

        $booking = Booking::where('id', $id)
            ->where('renter_id', $user->id)
            ->firstOrFail();

        if ($booking->status !== 'PENDING') {
            return ResponseHelper::error('لا يمكن حذف إلا الحجوزات المعلقة.', 400);
        }

        try {
            DB::beginTransaction();
            $booking->delete();
            DB::commit();

            return ResponseHelper::success(null, 'تم حذف الحجز بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في حذف الحجز: ' . $e->getMessage(), 500);
        }
    }
}
