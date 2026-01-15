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
            return new ApartmentPhotoResource($photo);
        })->toArray();

        // Format owner
        $owner = null;
        if ($this->owner) {
            $owner = new UserRecource($this->owner);
        }

        // Format reviews
        $reviews = $this->reviews->map(function ($review) {
            return new ReviewResource($review);
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
            'city_name' => $this->city?->name ?? '',
            'governorate_name' => $this->governorate?->name ?? '',
            'avg_rating' => $this->rating_avg ? (float) $this->rating_avg : null,
            'is_available' => $this->is_active,
            'is_favorite' => $isFavorite,
            'is_recommended' => (bool) $this->is_recommended,
            'images' => $images,
            'owner' => $owner,
            'reviews' => $reviews,
        ];

        // Return all data for both list and detail views
        return $data;
    }
}
