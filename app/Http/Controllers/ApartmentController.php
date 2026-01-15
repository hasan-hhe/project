<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ApartmentResource;
use App\Http\Requests\IndexApartmentRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Apartment;
use App\Models\Review;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class ApartmentController extends Controller
{
    #[OA\Get(
        path: "/apartments",
        summary: "Get all apartments",
        description: "Retrieve a paginated list of apartments with optional filters",
        tags: ["Apartments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "city_id", in: "query", description: "Filter by city ID", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "governorate_id", in: "query", description: "Filter by governorate ID", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "min_price", in: "query", description: "Minimum price filter", schema: new OA\Schema(type: "number", format: "float")),
            new OA\Parameter(name: "max_price", in: "query", description: "Maximum price filter", schema: new OA\Schema(type: "number", format: "float")),
            new OA\Parameter(name: "search", in: "query", description: "Search in title and description", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "sort_by", in: "query", description: "Sort field (created_at, price, etc.)", schema: new OA\Schema(type: "string", default: "created_at")),
            new OA\Parameter(name: "sort_dir", in: "query", description: "Sort direction", schema: new OA\Schema(type: "string", enum: ["asc", "desc"], default: "desc")),
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, default: 10)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Apartments retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Apartments retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function index(IndexApartmentRequest $request)
    {
        $data = $request->validated();
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

        // Search by title or description
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

        $perPage = min(max((int)($data['per_page'] ?? 10), 1), 50);
        $apartments = $query->paginate($perPage);

        return ResponseHelper::success([
            'apartments' => ApartmentResource::collection($apartments)
        ], 'تم جلب الشقق بنجاح.');
    }

    #[OA\Get(
        path: "/apartments/price-range",
        summary: "Get apartment price range",
        description: "Get minimum and maximum prices of all apartments",
        tags: ["Apartments"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Price range retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "min_price", type: "number", format: "float", example: 10000),
                                new OA\Property(property: "max_price", type: "number", format: "float", example: 100000),
                            ]
                        ),
                        new OA\Property(property: "body", type: "string", example: "Price range retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function getPriceRange(Request $request)
    {
        $minPrice = Apartment::min('price') ?? 0;
        $maxPrice = Apartment::max('price') ?? 0;

        return ResponseHelper::success([
            'min_price' => (float)$minPrice,
            'max_price' => (float)$maxPrice,
        ], 'تم جلب نطاق الأسعار بنجاح.');
    }

    // #[OA\Get(
    //     path: "/apartments/{id}",
    //     summary: "Get apartment by ID",
    //     description: "Retrieve detailed information about a specific apartment",
    //     tags: ["Apartments"],
    //     security: [["bearerAuth" => []]],
    //     parameters: [
    //         new OA\Parameter(name: "id", in: "path", required: true, description: "Apartment ID", schema: new OA\Schema(type: "integer")),
    //     ],
    //     responses: [
    //         new OA\Response(
    //             response: 200,
    //             description: "Apartment retrieved successfully",
    //             content: new OA\JsonContent(
    //                 properties: [
    //                     new OA\Property(property: "message", type: "string", example: "success"),
    //                     new OA\Property(property: "data", type: "object"),
    //                     new OA\Property(property: "body", type: "string", example: "Apartment retrieved successfully.")
    //                 ]
    //             )
    //         ),
    //         new OA\Response(response: 404, description: "Apartment not found"),
    //     ]
    // )]
    // public function show(Request $request, $id)
    // {
    //     $apartment = Apartment::with(['owner', 'city', 'governorate', 'photos', 'reviews.user', 'favorites'])
    //         ->findOrFail($id);
    //     return ResponseHelper::success(ApartmentResource::make($apartment), 'Apartment retrieved successfully.');
    // }

    #[OA\Get(
        path: "/apartments/favorites",
        summary: "Get favorite apartments",
        description: "Retrieve all apartments marked as favorite by the authenticated user",
        tags: ["Apartments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, default: 10)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Favorite apartments retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Favorite apartments retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function getFavoriteApartments(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('غير مصرح لك.', 401);
        }

        $perPage = min(max((int)$request->integer('per_page', 10), 1), 50);
        $favoriteApartments = Apartment::whereHas('favorites', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success([
            'favorite_apartments' => ApartmentResource::collection($favoriteApartments)
        ], 'تم جلب الشقق المفضلة بنجاح.');
    }

    #[OA\Post(
        path: "/apartments/{id}/toggle-favorite",
        summary: "Toggle favorite status",
        description: "Add or remove apartment from favorites",
        tags: ["Apartments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Apartment ID", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Favorite status updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "is_favorite", type: "boolean", example: true)
                            ]
                        ),
                        new OA\Property(property: "body", type: "string", example: "Favorite status updated successfully.")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Apartment not found"),
        ]
    )]
    public function toggleFavorite(Request $request, $id)
    {
        $user = $request->user();

        $apartment = Apartment::findOrFail($id);

        // Check if favorite exists
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

    #[OA\Get(
        path: "/apartments/{id}/reviews",
        summary: "Get apartment reviews",
        description: "Retrieve all reviews for a specific apartment",
        tags: ["Apartments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Apartment ID", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, default: 10)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Reviews retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Reviews retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function getReviews(Request $request, $id)
    {
        $perPage = min(max((int)$request->integer('per_page', 10), 1), 50);

        $reviews = Review::where('apartment_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success([
            'reviews' => ReviewResource::collection($reviews),
        ], 'تم جلب التقييمات بنجاح.');
    }

    #[OA\Post(
        path: "/apartments/{id}/reviews",
        summary: "Add review to apartment",
        description: "Submit a review and rating for an apartment",
        tags: ["Apartments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Apartment ID", schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["rating", "comment"],
                properties: [
                    new OA\Property(property: "rating", type: "integer", minimum: 1, maximum: 5, example: 5),
                    new OA\Property(property: "comment", type: "string", maxLength: 1000, example: "Great apartment with amazing view!"),
                    new OA\Property(property: "booking_id", type: "integer", nullable: true, description: "Optional booking ID if review is for a completed booking"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Review added successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Review added successfully.")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function addReview(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('غير مصرح لك.', 401);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);

        // if ($request->booking_id == null) {
        //     return ResponseHelper::error('لا يمكنك تقييم الشقة بدون حجز.', 400);
        // }

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

            // إعادة حساب متوسط التقييمات للشقة
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
