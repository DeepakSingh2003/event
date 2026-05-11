<?php

namespace App\Http\Resources;

use App\Support\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowResource extends JsonResource
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
            'show_date' => optional($this->show_date)?->toDateString(),
            'show_time' => optional($this->show_time)?->format('H:i'),
            'status' => $this->status,
            'booking_mode' => $this->booking_mode ?? 'reserved_seating',
            'price' => $this->price,
            'currency_code' => $this->currency_code ?? 'INR',
            'formatted_price' => Currency::format($this->price, $this->currency_code ?? 'INR', 0),
            'available_seats' => $this->available_seats,
            'sales_capacity' => $this->sales_capacity,
            'booking_open_at' => optional($this->booking_open_at)?->toIso8601String(),
            'booking_close_at' => optional($this->booking_close_at)?->toIso8601String(),
            'event' => [
                'id' => $this->event?->id,
                'title' => $this->event?->title,
                'slug' => $this->event?->slug,
                'description' => $this->event?->description,
                'category' => $this->event?->eventCategory?->name ?? $this->event?->category,
                'poster_image_url' => $this->imageUrl($this->event?->poster_image),
                'banner_image_url' => $this->imageUrl($this->event?->banner_image),
                'is_featured' => (bool) ($this->event?->is_featured ?? false),
            ],
            'venue' => [
                'id' => $this->venue?->id,
                'name' => $this->venue?->name,
                'city' => $this->venue?->cityRecord?->name ?? $this->venue?->city,
                'state' => $this->venue?->cityRecord?->state,
                'country_id' => $this->venue?->cityRecord?->country_id,
                'country' => $this->venue?->cityRecord?->countryRecord?->name ?? $this->venue?->cityRecord?->country,
                'address' => $this->venue?->address,
                'latitude' => $this->venue?->latitude,
                'longitude' => $this->venue?->longitude,
                'map_url' => $this->venue?->map_url,
                'layout_image_url' => $this->venue?->layout_image ? asset('storage/'.$this->venue->layout_image) : null,
                'layout_label' => $this->venue?->layout_label ?? 'SCREEN',
                'layout_label_position' => $this->venue?->layout_label_position ?? 'bottom',
            ],
            'ticket_types' => ShowTicketTypeResource::collection($this->whenLoaded('ticketTypes')),
            'seats' => SeatResource::collection($this->whenLoaded('seats')),
        ];
    }

    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
