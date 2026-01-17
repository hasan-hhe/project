<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ApartmentResource;
use App\Http\Requests\IndexApartmentRequest;
use App\Http\Resources\ReservationResource;
use App\Http\Resources\ReviewResource;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Favorite;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class ApartmentController extends Controller
{
    #[OA\Get(path: "/apartments", tags: ["Apartments"], security: [["bearerAuth" => []]])]
    public function index(Request $request)
    {
        try {
            $request->validate([
                'city_id' => ['nullable', 'integer', 'exists:cities,id'],
                'governorate_id' => ['nullable', 'integer', 'exists:governorates,id'],
                'min_price' => ['nullable', 'numeric', 'min:0'],
                'max_price' => ['nullable', 'numeric', 'min:0'],
                'sort_by' => ['nullable', 'in:price,created_at'],
                'sort_dir' => ['nullable', 'in:asc,desc'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        $data = $request->all();
        $query = Apartment::with(['owner', 'city', 'governorate', 'photos', 'reviews.user', 'favorites']);


        if (!empty($data['city_id'] ?? null)) {
            $query->where('city_id', $data['city_id']);
        }

        if (!empty($data['governorate_id'] ?? null)) {
            $query->where('governorate_id', $data['governorate_id']);
        }

        if (!empty($data['min_price'] ?? null)) {
            $query->where('price', '>=', $data['min_price']);
        }
        if (!empty($data['max_price'] ?? null)) {
            $query->where('price', '<=', $data['max_price']);
        }

        // search
        if (!empty($data['search'] ?? null)) {
            $search = $data['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy = $data['sort_by'] ?? 'created_at';
        $sortDir = $data['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        $perPage = $data['per_page'] ?? 10;
        $perPage = max(1, min(50, (int)$perPage));
        $apartments = $query->paginate($perPage);

        return ResponseHelper::success([
            'apartments' => ApartmentResource::collection($apartments)
        ], 'تم جلب الشقق بنجاح.');
    }

    #[OA\Get(path: "/apartments/price-range", tags: ["Apartments"], security: [["bearerAuth" => []]])]
    public function getPriceRange(Request $request)
    {
        $minPrice = Apartment::min('price') ?? 0;
        $maxPrice = Apartment::max('price') ?? 0;

        return ResponseHelper::success([
            'min_price' => (float)$minPrice,
            'max_price' => (float)$maxPrice,
        ], 'تم جلب نطاق الأسعار بنجاح.');
    }

    #[OA\Get(path: "/apartments/favorites", tags: ["Apartments"], security: [["bearerAuth" => []]])]
    public function getFavoriteApartments(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('غير مصرح لك.', 401);
        }

        $perPage = $request->get('per_page', 10);
        $perPage = max(1, min(50, (int)$perPage));
        $favoriteApartments = Apartment::whereHas('favorites', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success([
            'favorite_apartments' => ApartmentResource::collection($favoriteApartments)
        ], 'تم جلب الشقق المفضلة بنجاح.');
    }

    #[OA\Post(path: "/apartments/{id}/toggle-favorite", tags: ["Apartments"], security: [["bearerAuth" => []]])]
    public function toggleFavorite(Request $request, $id)
    {
        $user = $request->user();

        $apartment = Apartment::findOrFail($id);

        $favorite = Favorite::where('user_id', $user->id)
            ->where('apartment_id', $apartment->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $isFavorite = false;
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'apartment_id' => $apartment->id,
            ]);
            $isFavorite = true;
        }

        return ResponseHelper::success([
            'is_favorite' => $isFavorite,
        ], 'تم تحديث حالة المفضلة بنجاح.');
    }

    #[OA\Get(path: "/apartments/{id}/reviews", tags: ["Apartments"], security: [["bearerAuth" => []]])]
    public function getReviews(Request $request, $id)
    {
        $perPage = $request->get('per_page', 10);
        $perPage = max(1, min(50, (int)$perPage));

        $reviews = Review::where('apartment_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success([
            'reviews' => ReviewResource::collection($reviews),
        ], 'تم جلب التقييمات بنجاح.');
    }

    #[OA\Post(path: "/apartments/{id}/reviews", tags: ["Apartments"], security: [["bearerAuth" => []]])]
    public function addReview(Request $request, $id)
    {
        $user = $request->user();

        try {
            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|max:1000',
                'booking_id' => 'nullable|exists:bookings,id',
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        $userBooking = Booking::where('renter_id', Auth::id())
            ->firstOrFail();

        if (!$userBooking) {
            return ResponseHelper::error('لا يمكنك تقييم الشقة بدون حجز.', 400);
        }

        $apartment = Apartment::findOrFail($id);

        try {
            DB::beginTransaction();

            $review = Review::create([
                'apartment_id' => $id,
                'user_id' => $user->id,
                'booking_id' => $request->booking_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            $averageRating = $apartment->reviews()->avg('rating');
            $apartment->update([
                'rating_avg' => round($averageRating, 2)
            ]);

            DB::commit();

            $review->load('user');

            return ResponseHelper::success([
                'review' => new ReviewResource($review)
            ], 'تم إضافة التقييم بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في إضافة التقييم: ' . $e->getMessage(), 500);
        }
    }
}
