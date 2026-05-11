<?php

namespace App\Http\Resources;

use App\Support\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowTicketTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $remaining = $this->capacity === null
            ? null
            : max((int) $this->capacity - (int) $this->sold_count, 0);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'price' => $this->price,
            'formatted_price' => Currency::format($this->price, $this->show?->currency_code ?? 'INR', 0),
            'capacity' => $this->capacity,
            'sold_count' => (int) $this->sold_count,
            'remaining' => $remaining,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
