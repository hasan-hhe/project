<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MesssageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing('sender');

        $currentUserId = $request->user()?->id;
        $isMe = $currentUserId && $this->sender_id == $currentUserId;

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'text' => $this->content,
            'content' => $this->content, // Alias for compatibility
            'attachment_url' => $this->attachment_url,
            'sent_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'read_at' => $this->read_at?->format('Y-m-d H:i:s'),
            'is_me' => $isMe,
            'sender_name' => $this->sender ? ($this->sender->first_name . ' ' . $this->sender->last_name) : null,
            'sender_avatar' => $this->sender?->avatar_url,
            'sender' => $this->sender ? new UserRecource($this->sender) : null,
        ];
    }
}
