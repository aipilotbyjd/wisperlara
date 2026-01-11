<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trigger_phrase' => $this->trigger_phrase,
            'replacement_text' => $this->replacement_text,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
