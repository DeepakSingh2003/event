<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'booking_reference' => $this->booking_reference,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_gateway' => $this->payment_gateway,
            'payment_id' => $this->payment_id,
            'payment_proof_url' => $this->payment_proof_path ? asset('storage/'.$this->payment_proof_path) : null,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'seats' => $this->seats,
            'booked_at' => optional($this->booked_at)?->toIso8601String(),
            'confirmed_at' => optional($this->confirmed_at)?->toIso8601String(),
            'ticket_url' => $this->status === 'confirmed' ? route('api.bookings.ticket', $this->id) : null,
            'event' => [
                'id' => $this->event?->id,
                'title' => $this->event?->title,
            ],
            'show' => new ShowResource($this->whenLoaded('show')),
            'items' => $this->items?->map(fn ($item) => [
                'seat_number' => $item->seat_number,
                'seat_type_name' => $item->seat_type_name,
                'quantity' => (int) ($item->quantity ?? 1),
                'unit_price' => $item->unit_price,
                'status' => $item->status,
            ]),
        ];
    }
}
