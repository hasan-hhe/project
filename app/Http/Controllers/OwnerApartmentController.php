<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ApartmentPhotoResource;
use App\Http\Resources\ApartmentResource;
use App\Http\Resources\ReservationResource;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Photo;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Notification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

use function App\Helpers\sendNotification;
use function App\Helpers\sendNotificationToUser;
use function App\Helpers\uploadImage;

class OwnerApartmentController extends Controller
{
    #[OA\Get(path: "/owner/apartments", tags: ["Owner"], security: [["bearerAuth" => []]])]
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->account_type !== 'OWNER') {
            return ResponseHelper::error('يمكن لمالكي الشقق فقط الوصول إلى هذا.', 403);
        }

        $perPage = $request->get('per_page', 10);
        $perPage = max(1, min(50, (int)$perPage));

        $apartments = Apartment::where('owner_id', $user->id)
            ->with(['city', 'governorate', 'photos', 'bookings', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success([
            'apartments' => ApartmentResource::collection($apartments)
        ], 'تم جلب الشقق بنجاح.');
    }

    #[OA\Get(path: "/owner/apartments/locations", tags: ["Owner"], security: [["bearerAuth" => []]])]
    public function getLocations(Request $request)
    {
        $user = $request->user();
        if ($user->account_type !== 'OWNER') {
            return ResponseHelper::error('يمكن لمالكي الشقق فقط الوصول إلى هذا.', 403);
        }

        $governorates = Governorate::with('cities')->orderBy('name', 'asc')->get();
        $cities = City::with('governorate')->orderBy('name', 'asc')->get();

        return ResponseHelper::success([
            'governorates' => $governorates,
            'cities' => $cities,
        ], 'تم جلب المواقع بنجاح.');
    }

    #[OA\Post(path: "/owner/apartments", tags: ["Owner"], security: [["bearerAuth" => []]])]
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('غير مصرح. يمكن لمالكي الشقق فقط الوصول إلى هذا.', 403);
        }

        if ($user->status !== 'APPROVED') {
            return ResponseHelper::error('حسابك غير موافق عليه بعد. يرجى انتظار موافقة الإدارة.', 403);
        }

        try {
            $request->validate([
                'governorate_id' => 'required|exists:governorates,id',
                'city_id' => 'required|exists:cities,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'rooms_count' => 'required|integer|min:1',
                'address_line' => 'required|string|max:255',
                'photos' => 'nullable|array',
                'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        try {
            DB::beginTransaction();

            $apartment = Apartment::create([
                'owner_id' => $user->id,
                'governorate_id' => $request->governorate_id,
                'city_id' => $request->city_id,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'rooms_count' => $request->rooms_count,
                'address_line' => $request->address_line,
                'rating_avg' => 5.0,
                'is_active' => false,
                'is_recommended' => false,
            ]);

            if ($request->hasFile('photos')) {
                $maxSortOrder = 0;
                foreach ($request->file('photos') as $index => $photo) {
                    $url = uploadImage($photo, 'apartments/photos', 'public');
                    $isCover = $index === 0;

                    Photo::create([
                        'apartment_id' => $apartment->id,
                        'url' => $url,
                        'is_cover' => $isCover,
                        'sort_order' => $maxSortOrder++,
                    ]);
                }
            }

            $apartment->load(['city', 'governorate', 'photos', 'owner']);

            DB::commit();

            return ResponseHelper::success([
                'apartment' => new ApartmentResource($apartment)
            ], 'تم إنشاء الشقة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في إنشاء الشقة: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(path: "/owner/apartments/{id}", tags: ["Owner"], security: [["bearerAuth" => []]])]
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('غير مصرح. يمكن لمالكي الشقق فقط الوصول إلى هذا.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        try {
            $request->validate([
                'governorate_id' => 'sometimes|required|exists:governorates,id',
                'city_id' => 'sometimes|required|exists:cities,id',
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'price' => 'sometimes|required|numeric|min:0',
                'rooms_count' => 'sometimes|required|integer|min:1',
                'address_line' => 'sometimes|required|string|max:255',
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        try {
            DB::beginTransaction();

            $apartment->update($request->only([
                'governorate_id',
                'city_id',
                'title',
                'description',
                'price',
                'rooms_count',
                'address_line',
            ]));

            $apartment->load(['city', 'governorate', 'photos', 'owner']);

            DB::commit();

            return ResponseHelper::success([
                'apartment' => new ApartmentResource($apartment),
            ], 'تم تحديث الشقة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في تحديث الشقة: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(path: "/owner/apartments/{id}", tags: ["Owner"], security: [["bearerAuth" => []]])]
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('غير مصرح. يمكن لمالكي الشقق فقط الوصول إلى هذا.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $activeBookings = $apartment->bookings()
            ->whereIn('status', ['PENDING', 'CONFIRMED'])
            ->count();

        if ($activeBookings > 0) {
            return ResponseHelper::error('لا يمكن حذف الشقة مع وجود حجوزات نشطة.', 400);
        }

        try {
            DB::beginTransaction();

            foreach ($apartment->photos as $photo) {
                if (Storage::disk('public')->exists($photo->url)) {
                    Storage::disk('public')->delete($photo->url);
                }
                $photo->delete();
            }

            $apartment->delete();

            DB::commit();

            return ResponseHelper::success(null, 'تم حذف الشقة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في حذف الشقة: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(path: "/owner/apartments/{id}/photos", tags: ["Owner"], security: [["bearerAuth" => []]])]
    public function getPhotos(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('غير مصرح. يمكن لمالكي الشقق فقط الوصول إلى هذا.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $photos = $apartment->photos()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return ResponseHelper::success([
            'photos' => ApartmentPhotoResource::collection($photos)
        ], 'تم جلب الصور بنجاح.');
    }

    #[OA\Post(path: "/owner/apartments/{id}/photos", tags: ["Owner"], security: [["bearerAuth" => []]])]
    public function uploadPhotos(Request $request, $id)
    {
        $user = $request->user();
        if ($user->account_type !== 'OWNER') {
            return ResponseHelper::error('Unauthorized. Only apartment owners can access this.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        try {
            $request->validate([
                'photos' => 'required|array|min:1',
                'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        try {
            DB::beginTransaction();

            $maxSortOrder = $apartment->photos()->max('sort_order') ?? 0;
            $uploadedPhotos = [];

            foreach ($request->file('photos') as $photo) {
                $url = uploadImage($photo, 'apartments/photos', 'public');

                $uploadedPhoto = Photo::create([
                    'apartment_id' => $apartment->id,
                    'url' => $url,
                    'is_cover' => false,
                    'sort_order' => ++$maxSortOrder,
                ]);

                $uploadedPhotos[] = $uploadedPhoto;
            }

            DB::commit();

            return ResponseHelper::success([
                'photos' => ApartmentPhotoResource::collection($uploadedPhotos)
            ], 'تم رفع الصور بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في رفع الصور: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(path: "/owner/apartments/{id}/photos/{photoId}", tags: ["Owner"], security: [["bearerAuth" => []]])]
    public function deletePhoto(Request $request, $id, $photoId)
    {
        $user = $request->user();
        if ($user->account_type !== 'OWNER') {
            return ResponseHelper::error('يمكن لمالكي الشقق فقط الوصول إلى هذا.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $photo = Photo::where('id', $photoId)
            ->where('apartment_id', $apartment->id)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            if (Storage::disk('public')->exists($photo->url)) {
                Storage::disk('public')->delete($photo->url);
            }

            $photo->delete();

            DB::commit();

            return ResponseHelper::success(null, 'تم حذف الصورة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في حذف الصورة: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(path: "/owner/apartments/{id}/photos/{photoId}/set-cover", tags: ["Owner"], security: [["bearerAuth" => []]])]
    public function setCoverPhoto(Request $request, $id, $photoId)
    {
        $user = $request->user();
        if ($user->account_type !== 'OWNER') {
            return ResponseHelper::error('يمكن لمالكي الشقق فقط الوصول إلى هذا.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $photo = Photo::where('id', $photoId)
            ->where('apartment_id', $apartment->id)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $apartment->photos()->update(['is_cover' => false]);
            $photo->update(['is_cover' => true]);

            DB::commit();

            return ResponseHelper::success([
                'photo' => new ApartmentPhotoResource($photo)
            ], 'تم تحديث صورة الغلاف بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في تعيين صورة الغلاف: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(path: "/owner/apartments/{id}/bookings", tags: ["Owner"], security: [["bearerAuth" => []]])]
    public function getBookings(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('غير مصرح. يمكن لمالكي الشقق فقط الوصول إلى هذا.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $perPage = $request->get('per_page', 10);
        $perPage = max(1, min(50, (int)$perPage));

        $bookings = $apartment->bookings()
            ->with('renter')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $bookings->getCollection()->transform(function ($booking) {
            $booking->has_change_request = !empty($booking->change_reason);
            return $booking;
        });

        return ResponseHelper::success([
            'bookings' => ReservationResource::collection($bookings)
        ], 'تم جلب الحجوزات بنجاح.');
    }

    #[OA\Post(path: "/owner/apartments/{id}/bookings/{bookingId}/approve", tags: ["Owner"], security: [["bearerAuth" => []]])]
    public function approveBooking(Request $request, $id, $bookingId)
    {
        $owner = $request->user();
        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $owner->id)
            ->firstOrFail();

        $booking = Booking::where('id', $bookingId)
            ->where('apartment_id', $apartment->id)
            ->firstOrFail();

        if ($booking->status !== 'PENDING') {
            return ResponseHelper::error('يمكن الموافقة على الحجوزات المعلقة فقط.', 400);
        }

        $renter = $booking->renter;

        if ($renter->wallet_balance < $booking->total_price) {
            return ResponseHelper::error('رصيد المستأجر غير كافٍ.', 400);
            $booking->update([
                'status' => 'REJECTED',
            ]);

            sendNotificationToUser($renter->id, 'تم رفض الحجز', 'تم رفض حجزك من قبل المالك.');
        }

        try {
            DB::beginTransaction();

            $owner->wallet_balance += $booking->total_price;
            $owner->save();

            $renter->wallet_balance -= $booking->total_price;
            $renter->save();

            $booking->update([
                'status' => 'CONFIRMED',
            ]);

            $booking->load(['apartment', 'renter']);

            DB::commit();

            sendNotificationToUser($renter->id, 'تم الموافقة على الحجز', 'تم الموافقة على حجزك من قبل المالك.');

            return ResponseHelper::success(null, 'تم الموافقة على الحجز بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في الموافقة على الحجز: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(path: "/owner/apartments/{id}/bookings/{bookingId}/reject", tags: ["Owner"], security: [["bearerAuth" => []]])]
    public function rejectBooking(Request $request, $id, $bookingId)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('غير مصرح. يمكن لمالكي الشقق فقط الوصول إلى هذا.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $booking = \App\Models\Booking::where('id', $bookingId)
            ->where('apartment_id', $apartment->id)
            ->firstOrFail();

        if ($booking->status !== 'PENDING') {
            return ResponseHelper::error('يمكن رفض الحجوزات المعلقة فقط.', 400);
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $booking->update([
                'status' => 'REJECTED',
                'rejection_reason' => $request->rejection_reason,
            ]);

            $booking->load(['apartment', 'renter']);

            DB::commit();

            return ResponseHelper::success(null, 'تم رفض الحجز بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في رفض الحجز: ' . $e->getMessage(), 500);
        }
    }
}
