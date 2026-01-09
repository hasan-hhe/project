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
        $sender = User::findorFail($this->sender_id);
        return [
            'content' => $this->content,
            'sender' => $sender->fullName(),
            'attachment_url' => $this->attachment_url
        ];
    }
}
