<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing('user');

        return [
            'id' => $this->id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'renter_name' => $this->user ? ($this->user->first_name . ' ' . $this->user->last_name) : null,
            'renter_avatar' => $this->user?->avatar_url
        ];
    }
}
