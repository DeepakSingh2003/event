<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShowSeat extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_LOCKED = 'locked';
    public const STATUS_BOOKED = 'booked';
    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'show_id',
        'seat_type_id',
        'locked_by',
        'booking_id',
        'row_label',
        'column_number',
        'seat_number',
        'base_price',
        'price',
        'status',
        'locked_until',
        'booked_at',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'price' => 'decimal:2',
        'locked_until' => 'datetime',
        'booked_at' => 'datetime',
    ];

    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class);
    }

    public function seatType(): BelongsTo
    {
        return $this->belongsTo(SeatType::class);
    }

    public function locker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
