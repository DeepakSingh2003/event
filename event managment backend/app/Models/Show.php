<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Show extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'venue_id',
        'show_date',
        'show_time',
        'price',
        'currency_code',
        'available_seats',
        'sales_capacity',
        'booking_mode',
        'status',
        'booking_open_at',
        'booking_close_at',
        'seat_lock_minutes',
        'seat_map_generated_at',
    ];

    protected $casts = [
        'show_date' => 'date',
        'show_time' => 'datetime:H:i',
        'price' => 'decimal:2',
        'booking_open_at' => 'datetime',
        'booking_close_at' => 'datetime',
        'seat_map_generated_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(ShowSeat::class)->orderBy('row_label')->orderBy('column_number');
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(ShowTicketType::class);
    }

}
