<?php

namespace App\Http\Resources;

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
        $this->loadMissing(['owner', 'renter', 'apartment', 'messages']);

        // Get last message
        $lastMessage = $this->messages()->latest()->first();

        // Count unread messages for current user
        $unreadCount = 0;
        if ($request->user()) {
            $unreadCount = $this->messages()
                ->where('sender_id', '!=', $request->user()->id)
                ->whereNull('read_at')
                ->count();
        }

        return [
            'id' => $this->id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'last_message_at' => $lastMessage?->created_at?->format('Y-m-d H:i:s'),
            'owner' => $this->owner ? new UserRecource($this->owner) : null,
            'renter' => $this->renter ? new UserRecource($this->renter) : null,
            'apartment' => $this->apartment ? new ApartmentResource($this->apartment) : null,
            'messages' => $this->whenLoaded('messages', function () {
                return $this->messages->map(function ($message) {
                    return new MesssageResource($message);
                });
            }),
            'unread_count' => $unreadCount,
        ];
    }
}
