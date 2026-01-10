<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ApartmentResource;
use App\Models\Apartment;
use App\Models\Photo;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

use function App\Helpers\uploadImage;

class OwnerApartmentController extends Controller
{
    #[OA\Get(
        path: "/owner/apartments",
        summary: "Get owner apartments",
        description: "Retrieve all apartments owned by the authenticated user (OWNER account type required)",
        tags: ["Owner"],
        security: [["bearerAuth" => []]],
        parameters: [
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
            new OA\Response(response: 403, description: "Unauthorized - Only OWNER account type can access"),
        ]
    )]
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('Unauthorized. Only apartment owners can access this.', 403);
        }

        $perPage = min(max((int)$request->integer('per_page', 10), 1), 50);

        $apartments = Apartment::where('owner_id', $user->id)
            ->with(['city', 'governorate', 'photos', 'bookings', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success(ApartmentResource::collection($apartments), 'Apartments retrieved successfully.');
    }

    #[OA\Get(
        path: "/owner/apartments/locations",
        summary: "Get locations for apartment creation",
        description: "Get governorates and cities for creating apartments (OWNER account type required)",
        tags: ["Owner"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Locations retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Locations retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function getLocations(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('Unauthorized. Only apartment owners can access this.', 403);
        }

        $governorates = Governorate::with('cities')->orderBy('name', 'asc')->get();
        $cities = City::with('governorate')->orderBy('name', 'asc')->get();

        return ResponseHelper::success([
            'governorates' => $governorates,
            'cities' => $cities,
        ], 'Locations retrieved successfully.');
    }

    #[OA\Get(
        path: "/owner/apartments/{id}",
        summary: "Get owner apartment by ID",
        description: "Retrieve detailed information about a specific apartment owned by the authenticated user (OWNER account type required)",
        tags: ["Owner"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Apartment ID", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Apartment retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Apartment retrieved successfully.")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Apartment not found"),
        ]
    )]
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('Unauthorized. Only apartment owners can access this.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->with(['city', 'governorate', 'photos', 'bookings.renter', 'reviews.user'])
            ->firstOrFail();

        return ResponseHelper::success(ApartmentResource::make($apartment), 'Apartment retrieved successfully.');
    }

    #[OA\Post(
        path: "/owner/apartments",
        summary: "Create new apartment",
        description: "Create a new apartment listing (OWNER account type required, account must be APPROVED)",
        tags: ["Owner"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["governorate_id", "city_id", "title", "description", "price", "rooms_count", "address_line"],
                    properties: [
                        new OA\Property(property: "governorate_id", type: "integer", example: 1),
                        new OA\Property(property: "city_id", type: "integer", example: 1),
                        new OA\Property(property: "title", type: "string", maxLength: 255, example: "Beautiful Apartment in Downtown"),
                        new OA\Property(property: "description", type: "string", example: "Spacious apartment with amazing view"),
                        new OA\Property(property: "price", type: "number", format: "float", minimum: 0, example: 150.50),
                        new OA\Property(property: "rooms_count", type: "integer", minimum: 1, example: 3),
                        new OA\Property(property: "address_line", type: "string", maxLength: 255, example: "123 Main Street"),
                        new OA\Property(property: "photos", type: "array", items: new OA\Items(type: "string", format: "binary"), description: "Apartment photos (optional)"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Apartment created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Apartment created successfully.")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Unauthorized or account not approved"),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('Unauthorized. Only apartment owners can access this.', 403);
        }

        // Check if user is approved
        if ($user->status !== 'APPROVED') {
            return ResponseHelper::error('Your account is not approved yet. Please wait for admin approval.', 403);
        }

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
                'is_active' => false, // Default inactive until admin approves
                'is_favorite' => false,
            ]);

            // Upload photos if provided
            if ($request->hasFile('photos')) {
                $maxSortOrder = 0;
                foreach ($request->file('photos') as $index => $photo) {
                    $url = uploadImage($photo, 'apartments/photos', 'public');
                    $isCover = $index === 0; // First photo is cover

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

            return ResponseHelper::success(ApartmentResource::make($apartment), 'Apartment created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to create apartment: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: "/owner/apartments/{id}",
        summary: "Update apartment",
        description: "Update an existing apartment (OWNER account type required)",
        tags: ["Owner"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Apartment ID", schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "governorate_id", type: "integer", nullable: true),
                    new OA\Property(property: "city_id", type: "integer", nullable: true),
                    new OA\Property(property: "title", type: "string", maxLength: 255, nullable: true),
                    new OA\Property(property: "description", type: "string", nullable: true),
                    new OA\Property(property: "price", type: "number", format: "float", minimum: 0, nullable: true),
                    new OA\Property(property: "rooms_count", type: "integer", minimum: 1, nullable: true),
                    new OA\Property(property: "address_line", type: "string", maxLength: 255, nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Apartment updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Apartment updated successfully.")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Unauthorized"),
        ]
    )]
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('Unauthorized. Only apartment owners can access this.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'governorate_id' => 'sometimes|required|exists:governorates,id',
            'city_id' => 'sometimes|required|exists:cities,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'rooms_count' => 'sometimes|required|integer|min:1',
            'address_line' => 'sometimes|required|string|max:255',
        ]);

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

            return ResponseHelper::success(ApartmentResource::make($apartment), 'Apartment updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to update apartment: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: "/owner/apartments/{id}",
        summary: "Delete apartment",
        description: "Permanently delete an apartment (OWNER account type required, cannot delete if has active bookings)",
        tags: ["Owner"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Apartment ID", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Apartment deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Apartment deleted successfully.")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Cannot delete apartment with active bookings"),
            new OA\Response(response: 403, description: "Unauthorized"),
        ]
    )]
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('Unauthorized. Only apartment owners can access this.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        // Check if apartment has active bookings
        $activeBookings = $apartment->bookings()
            ->whereIn('status', ['PENDING', 'CONFIRMED'])
            ->count();

        if ($activeBookings > 0) {
            return ResponseHelper::error('Cannot delete apartment with active bookings.', 400);
        }

        try {
            DB::beginTransaction();

            // Delete photos
            foreach ($apartment->photos as $photo) {
                if (Storage::disk('public')->exists($photo->url)) {
                    Storage::disk('public')->delete($photo->url);
                }
                $photo->delete();
            }

            $apartment->delete();

            DB::commit();

            return ResponseHelper::success(null, 'Apartment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to delete apartment: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: "/owner/apartments/{id}/photos",
        summary: "Get apartment photos",
        description: "Retrieve all photos for an apartment (OWNER account type required)",
        tags: ["Owner"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Apartment ID", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Photos retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")),
                        new OA\Property(property: "body", type: "string", example: "Photos retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function getPhotos(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('Unauthorized. Only apartment owners can access this.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $photos = $apartment->photos()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return ResponseHelper::success($photos, 'Photos retrieved successfully.');
    }

    /**
     * Upload photos for apartment
     */
    #[OA\Post(
        path: "/owner/apartments/{id}/photos",
        summary: "Upload apartment photos",
        description: "Upload one or more photos for an apartment (OWNER account type required)",
        tags: ["Owner"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Apartment ID", schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["photos"],
                    properties: [
                        new OA\Property(property: "photos", type: "array", items: new OA\Items(type: "string", format: "binary"), description: "Photo files (array)"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Photos uploaded successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")),
                        new OA\Property(property: "body", type: "string", example: "Photos uploaded successfully.")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function uploadPhotos(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('Unauthorized. Only apartment owners can access this.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

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

            return ResponseHelper::success($uploadedPhotos, 'Photos uploaded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to upload photos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete photo
     */
    #[OA\Delete(
        path: "/owner/apartments/{id}/photos/{photoId}",
        summary: "Delete apartment photo",
        description: "Delete a specific photo from an apartment (OWNER account type required)",
        tags: ["Owner"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Apartment ID", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "photoId", in: "path", required: true, description: "Photo ID", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Photo deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Photo deleted successfully.")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Photo not found"),
        ]
    )]
    public function deletePhoto(Request $request, $id, $photoId)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('Unauthorized. Only apartment owners can access this.', 403);
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

            return ResponseHelper::success(null, 'Photo deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to delete photo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Set cover photo
     */
    #[OA\Post(
        path: "/owner/apartments/{id}/photos/{photoId}/set-cover",
        summary: "Set cover photo",
        description: "Set a specific photo as the cover photo for an apartment (OWNER account type required)",
        tags: ["Owner"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Apartment ID", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "photoId", in: "path", required: true, description: "Photo ID", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Cover photo set successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Cover photo set successfully.")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Photo not found"),
        ]
    )]
    public function setCoverPhoto(Request $request, $id, $photoId)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('Unauthorized. Only apartment owners can access this.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $photo = Photo::where('id', $photoId)
            ->where('apartment_id', $apartment->id)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // Remove cover from all photos
            $apartment->photos()->update(['is_cover' => false]);

            // Set new cover
            $photo->update(['is_cover' => true]);

            DB::commit();

            return ResponseHelper::success($photo, 'Cover photo updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to set cover photo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get apartment bookings
     */
    #[OA\Get(
        path: "/owner/apartments/{id}/bookings",
        summary: "Get apartment bookings",
        description: "Retrieve all bookings for a specific apartment (OWNER account type required)",
        tags: ["Owner"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Apartment ID", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, default: 10)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Bookings retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Bookings retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function getBookings(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->account_type !== 'OWNER') {
            return ResponseHelper::error('Unauthorized. Only apartment owners can access this.', 403);
        }

        $apartment = Apartment::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $perPage = min(max((int)$request->integer('per_page', 10), 1), 50);

        $bookings = $apartment->bookings()
            ->with('renter')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success($bookings, 'Bookings retrieved successfully.');
    }

}

