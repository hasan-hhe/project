<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        if ($request->route()->getName() == 'getApar') {
            return [
                'id' => $this->id,
                'name' => $this->title,
                // 'description' => $this->description,
                'price_per_month' => $this->price,
                // 'available' => $this->is_active,
            ];
        }
        if ($request->route()->getName() == 'getAparById') {
            
        $url = $this->apartment->cover();
            return [
                'name' => $this->title,
                'description' => $this->description,
                'price_per_month' => $this->price,
                'rating' => $this->rating_avg,
                'photosURL' => $this->$url,
            ];
        }

        if ($request->route()->getName() == 'getFavoriteApar') {
            return [
                'id' => $this->id,
                'name' => $this->title,
                'price_per_month' => $this->price,
                'photosURL' => $this->photosURL
            ];
        }

        return parent::toArray($request);
    }
}
