<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'state' => $this->state,
            'country_id' => $this->country_id,
            'country' => $this->country,
            'country_name' => $this->countryRecord?->name ?? $this->country,
            'is_active' => (bool) $this->is_active,
            'venues_count' => (int) ($this->venues_count ?? 0),
            'events_count' => (int) ($this->events_count ?? 0),
        ];
    }
}