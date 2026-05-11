<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'address' => $this->address,
            'total_seats' => $this->total_seats,
            'row_count' => $this->row_count,
            'column_count' => $this->column_count,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'map_url' => $this->map_url,
            'layout_image_url' => $this->layout_image ? asset('storage/'.$this->layout_image) : null,
            'layout_label' => $this->layout_label ?? 'SCREEN',
            'layout_label_position' => $this->layout_label_position ?? 'bottom',
            'shows_count' => $this->whenCounted('shows'),
            'city' => [
                'id' => $this->cityRecord?->id,
                'name' => $this->cityRecord?->name ?? $this->city,
                'state' => $this->cityRecord?->state,
                'country_id' => $this->cityRecord?->country_id,
                'country' => $this->cityRecord?->countryRecord?->name ?? $this->cityRecord?->country,
            ],
        ];
    }
}
