<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StylePreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'app_identifier' => $this->app_identifier,
            'app_name' => $this->app_name,
            'style' => $this->style,
            'created_at' => $this->created_at,
        ];
    }
}
