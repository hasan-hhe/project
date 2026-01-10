<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserRecource extends JsonResource
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
            'name' => $this->first_name . ' ' . $this->last_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'avatar_url' => $this->avatar_url,
            'identity_document_url' => $this->identity_document_url,
            'date_of_birth' => $this->date_of_birth,
            'account_type' => $this->account_type,
            'phone_number' => $this->phone_number,
            'wallet_balance' => $this->wallet_balance,
        ];
    }
}
