<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'minutes_used' => $this['minutes_used'],
            'minutes_limit' => $this['minutes_limit'],
            'transcription_count' => $this['transcription_count'],
            'days_remaining' => $this['days_remaining'],
            'plan' => $this['plan'],
            'is_unlimited' => $this['is_unlimited'],
            'usage_percentage' => $this['usage_percentage'],
        ];
    }
}
