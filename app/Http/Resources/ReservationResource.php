<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing(['apartment.photos', 'renter']);

        // Get cover photo
        $coverPhoto = $this->apartment?->photos()->where('is_cover', true)->first();
        $apartmentImage = $coverPhoto?->url ?? ($this->apartment?->photos->first()?->url ?? null);

        return [
            'id' => $this->id,
            'apartment_id' => $this->apartment_id,
            'apartment_title' => $this->apartment?->title,
            'apartment_image' => $apartmentImage,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'total_price' => (float) $this->total_price,
            'created_at' => $this->created_at,
            'renter' => $this->renter ? new UserRecource($this->renter) : null,
            'apartment' => $this->apartment ? new ApartmentResource($this->apartment) : null,
        ];
    }
}
