<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DictionaryWordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'word' => $this->word,
            'category' => $this->category,
            'pronunciation' => $this->pronunciation,
            'created_at' => $this->created_at,
        ];
    }
}
