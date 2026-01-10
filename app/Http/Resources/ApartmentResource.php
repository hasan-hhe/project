<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Load relationships if not already loaded
        $this->loadMissing(['owner', 'city', 'governorate', 'photos', 'reviews.user']);

        // Check if user has favorited this apartment
        $isFavorite = false;
        if ($request->user()) {
            $isFavorite = $this->favorites()
                ->where('user_id', $request->user()->id)
                ->exists();
        }

        // Format photos
        $images = $this->photos->map(function ($photo) {
            return [
                'id' => $photo->id,
                'apartment_id' => $photo->apartment_id,
                'url' => $photo->url,
                'is_cover' => $photo->is_cover,
                'sort_order' => $photo->sort_order,
                'created_at' => $photo->created_at?->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        // Format owner
        $owner = null;
        if ($this->owner) {
            $owner = [
                'id' => $this->owner->id,
                'name' => $this->owner->first_name . ' ' . $this->owner->last_name,
                'first_name' => $this->owner->first_name,
                'last_name' => $this->owner->last_name,
                'phone_number' => $this->owner->phone_number,
                'email' => $this->owner->email,
                'avatar_url' => $this->owner->avatar_url,
            ];
        }

        // Format reviews
        $reviews = $this->reviews->map(function ($review) {
            return [
                'id' => $review->id,
                'apartment_id' => $review->apartment_id,
                'user_id' => $review->user_id,
                'booking_id' => $review->booking_id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'user' => $review->user ? [
                    'id' => $review->user->id,
                    'first_name' => $review->user->first_name,
                    'last_name' => $review->user->last_name,
                    'avatar_url' => $review->user->avatar_url,
                ] : null,
                'created_at' => $review->created_at?->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        // Base data structure matching Flutter model
        $data = [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'city_id' => $this->city_id,
            'governorate_id' => $this->governorate_id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price,
            'rooms_counter' => $this->rooms_count,
            'address_line' => $this->address_line,
            'location' => $this->city?->name ?? '',
            'city_name' => $this->city?->name ?? '',
            'governorate_name' => $this->governorate?->name ?? '',
            'avg_rating' => $this->rating_avg ? (float) $this->rating_avg : null,
            'is_available' => $this->is_active,
            'is_favorite' => $isFavorite,
            'images' => $images,
            'photos' => $images, // Alias for compatibility
            'owner' => $owner,
            'reviews' => $reviews,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];

        // Return all data for both list and detail views
        return $data;
    }
}
