<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Booking;
use App\Models\Apartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class BookingController extends Controller
{
    #[OA\Get(
        path: "/reservations/my-reservations",
        summary: "Get my reservations",
        description: "Retrieve all reservations for the authenticated user",
        tags: ["Reservations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, default: 10)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Reservations retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Reservations retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function getMyReservations(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('Unauthenticated.', 401);
        }

        $perPage = min(max((int)$request->integer('per_page', 10), 1), 50);
        
        $bookings = Booking::where('renter_id', $user->id)
            ->with(['apartment', 'apartment.owner'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success($bookings, 'Reservations retrieved successfully.');
    }

    #[OA\Get(
        path: "/reservations/{id}",
        summary: "Get reservation by ID",
        description: "Retrieve detailed information about a specific reservation",
        tags: ["Reservations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Reservation ID", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Reservation retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Reservation retrieved successfully.")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Reservation not found"),
        ]
    )]
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('Unauthenticated.', 401);
        }

        $booking = Booking::where('id', $id)
            ->where('renter_id', $user->id)
            ->with(['apartment', 'apartment.owner', 'reviews'])
            ->firstOrFail();

        return ResponseHelper::success($booking, 'Reservation retrieved successfully.');
    }

    #[OA\Post(
        path: "/reservations",
        summary: "Create new reservation",
        description: "Create a new booking/reservation for an apartment",
        tags: ["Reservations"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["apartment_id", "start_date", "end_date"],
                properties: [
                    new OA\Property(property: "apartment_id", type: "integer", example: 1),
                    new OA\Property(property: "start_date", type: "string", format: "date", example: "2026-01-15"),
                    new OA\Property(property: "end_date", type: "string", format: "date", example: "2026-01-20"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Reservation created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Reservation created successfully.")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Apartment not available for selected dates"),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('Unauthenticated.', 401);
        }

        $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $apartment = Apartment::findOrFail($request->apartment_id);

        // Check if apartment is available for the selected dates
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
            return ResponseHelper::error('Apartment is not available for the selected dates.', 400);
        }

        // Calculate total price
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $nights = $startDate->diffInDays($endDate);
        $totalPrice = $apartment->price * $nights;

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

            return ResponseHelper::success($booking, 'Reservation created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to create reservation: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: "/reservations/{id}/update",
        summary: "Update reservation",
        description: "Update an existing reservation (only pending reservations can be updated)",
        tags: ["Reservations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Reservation ID", schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "start_date", type: "string", format: "date", nullable: true, example: "2026-01-15"),
                    new OA\Property(property: "end_date", type: "string", format: "date", nullable: true, example: "2026-01-20"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Reservation updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Reservation updated successfully.")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Only pending reservations can be updated or apartment not available"),
        ]
    )]
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('Unauthenticated.', 401);
        }

        $booking = Booking::where('id', $id)
            ->where('renter_id', $user->id)
            ->firstOrFail();

        if ($booking->status !== 'PENDING') {
            return ResponseHelper::error('Only pending reservations can be updated.', 400);
        }

        $request->validate([
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        try {
            DB::beginTransaction();

            $startDate = $request->start_date ?? $booking->start_date;
            $endDate = $request->end_date ?? $booking->end_date;

            // Check if apartment is available for the new dates
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
                return ResponseHelper::error('Apartment is not available for the selected dates.', 400);
            }

            // Recalculate total price
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $nights = $start->diffInDays($end);
            $totalPrice = $booking->apartment->price * $nights;

            $booking->update([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_price' => $totalPrice,
            ]);

            $booking->load(['apartment', 'apartment.owner']);

            DB::commit();

            return ResponseHelper::success($booking, 'Reservation updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to update reservation: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: "/reservations/{id}/cancel",
        summary: "Cancel reservation",
        description: "Cancel an existing reservation",
        tags: ["Reservations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Reservation ID", schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "cancel_reason", type: "string", nullable: true, example: "Change of plans"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Reservation cancelled successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Reservation cancelled successfully.")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Reservation cannot be cancelled"),
        ]
    )]
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('Unauthenticated.', 401);
        }

        $booking = Booking::where('id', $id)
            ->where('renter_id', $user->id)
            ->firstOrFail();

        if (in_array($booking->status, ['CANCLED', 'COMPLETED'])) {
            return ResponseHelper::error('Reservation cannot be cancelled.', 400);
        }

        $request->validate([
            'cancel_reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $booking->update([
                'status' => 'CANCLED',
                'cancel_reason' => $request->cancel_reason ?? 'Cancelled by user',
            ]);

            DB::commit();

            return ResponseHelper::success($booking, 'Reservation cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to cancel reservation: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: "/reservations/{id}/delete",
        summary: "Delete reservation",
        description: "Permanently delete a reservation",
        tags: ["Reservations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Reservation ID", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Reservation deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Reservation deleted successfully.")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Reservation cannot be deleted"),
        ]
    )]
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('Unauthenticated.', 401);
        }

        $booking = Booking::where('id', $id)
            ->where('renter_id', $user->id)
            ->firstOrFail();

        if ($booking->status === 'CONFIRMED') {
            return ResponseHelper::error('Confirmed reservations cannot be deleted. Please cancel instead.', 400);
        }

        try {
            DB::beginTransaction();
            $booking->delete();
            DB::commit();

            return ResponseHelper::success(null, 'Reservation deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to delete reservation: ' . $e->getMessage(), 500);
        }
    }
}

