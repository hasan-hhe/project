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
        // If this is a UserNotification with notification relationship
        if ($this->notification) {
            return [
                'id' => $this->id, // UserNotification ID
                'type' => null,
                'title' => $this->notification->title,
                'message' => $this->notification->body,
                'body' => $this->notification->body,
                'is_read' => (bool) $this->is_seen,
                'read_at' => $this->is_seen ? $this->updated_at?->format('Y-m-d H:i:s') : null,
                'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
                'user_id' => $this->user_id,
                'related_type' => null,
                'related_id' => $this->notification_id,
            ];
        }
        
        // If this is a direct Notification model
        return [
            'id' => $this->id,
            'type' => null,
            'title' => $this->title,
            'message' => $this->body,
            'body' => $this->body,
            'is_read' => false,
            'read_at' => null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'user_id' => null,
            'related_type' => null,
            'related_id' => $this->id,
        ];
    }
}

