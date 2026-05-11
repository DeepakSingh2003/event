<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'show_seat_id',
        'show_ticket_type_id',
        'seat_number',
        'seat_type_name',
        'quantity',
        'unit_price',
        'status',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function showSeat(): BelongsTo
    {
        return $this->belongsTo(ShowSeat::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(ShowTicketType::class, 'show_ticket_type_id');
    }
}
