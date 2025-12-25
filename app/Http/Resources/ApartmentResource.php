<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {   if($request->route()->getName()=='getApar'){
        return [
            'id' => $this->id,
            'name' => $this->title,
           // 'description' => $this->description,
            'price_per_night' => $this->price,
           // 'available' => $this->is_active,
        ];
    }
    if($request->route()->getName()=='getAparById'){
        return [
            'name' => $this->title,
            'description' => $this->description,
            'price_per_night' => $this->price,
            'rating' => $this->rating
        ];
    }
        return parent::toArray($request);
    }
}
