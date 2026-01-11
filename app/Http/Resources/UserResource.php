<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'plan' => $this->plan,
            'preferred_language' => $this->preferred_language,
            'auto_detect_language' => $this->auto_detect_language,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
