<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeatResource extends JsonResource
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
            'seat_number' => $this->seat_number,
            'row_label' => $this->row_label,
            'column_number' => $this->column_number,
            'status' => $this->status,
            'price' => $this->price,
            'locked_until' => optional($this->locked_until)?->toIso8601String(),
            'seat_type' => $this->seatType?->name,
            'seat_type_color' => $this->seatType?->color,
        ];
    }
}
