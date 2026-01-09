<?php

namespace App\Http\Resources;

use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $url = $this->apartment->cover();
            return [
                'conversation_id' => $this->id,
                'apartment' => [
                    'name' => $this->apartment->title,
                    'image_url' => $url,
                ],
                'later_message' => $this->laterMessage(),
                'renter_user' => $this->renter->fullName(),
                'owner_user' => $this->owner->fullName(),
            ];
    }
}
