<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'plan' => $this->plan,
            'owner' => [
                'id' => $this->owner->id,
                'name' => $this->owner->full_name,
                'email' => $this->owner->email,
            ],
            'members_count' => $this->members->count(),
            'created_at' => $this->created_at,
        ];
    }
}
