<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => null,
            'title' => $this->title,
            'message' => $this->body,
            'body' => $this->body,
            'is_read' => false,
            'read_at' => null,
            'created_at' => $this->created_at,
            'user_id' => null,
            'related_type' => null,
            'related_id' => $this->id,
        ];
    }
}
