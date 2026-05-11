<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'iso_code' => $this->iso_code,
            'is_active' => (bool) ($this->is_active ?? true),
            'cities_count' => (int) ($this->cities_count ?? 0),
            'events_count' => (int) ($this->events_count ?? 0),
        ];
    }
}