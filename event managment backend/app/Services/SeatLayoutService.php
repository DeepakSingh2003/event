<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\SeatType;
use App\Models\Show;
use App\Models\ShowSeat;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SeatLayoutService
{
    public function generateForShow(Show $show, bool $force = false): void
    {
        if (! $force && $show->seats()->exists()) {
            return;
        }

        if ($show->seats()->where('status', ShowSeat::STATUS_BOOKED)->exists()) {
            throw ValidationException::withMessages([
                'show' => 'Seat layout cannot be regenerated after seats have been booked.',
            ]);
        }

        $seatTypes = $this->ensureDefaultSeatTypes()->keyBy('code');
        $rows = max(1, (int) $show->venue->row_count);
        $columns = max(1, (int) $show->venue->column_count);
        $totalSeats = $rows * $columns;

        DB::transaction(function () use ($show, $seatTypes, $rows, $columns, $totalSeats) {
            $show->seats()->delete();

            $seats = [];

            for ($rowIndex = 0; $rowIndex < $rows; $rowIndex++) {
                $rowLabel = $this->rowLabel($rowIndex);
                $code = $this->resolveSeatTypeCode($rowIndex, $rows);
                $seatType = $seatTypes[$code];

                for ($column = 1; $column <= $columns; $column++) {
                    $price = round((float) $show->price * (float) $seatType->price_multiplier, 2);

                    $seats[] = [
                        'show_id' => $show->id,
                        'seat_type_id' => $seatType->id,
                        'row_label' => $rowLabel,
                        'column_number' => $column,
                        'seat_number' => $rowLabel.$column,
                        'base_price' => $show->price,
                        'price' => $price,
                        'status' => ShowSeat::STATUS_AVAILABLE,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            ShowSeat::insert($seats);

            $show->update([
                'available_seats' => $totalSeats,
                'seat_map_generated_at' => now(),
            ]);
        });
    }

    public function releaseExpiredLocks(Show $show): void
    {
        $show->seats()
            ->where('status', ShowSeat::STATUS_LOCKED)
            ->whereNotNull('locked_until')
            ->where('locked_until', '<', now())
            ->update([
                'status' => ShowSeat::STATUS_AVAILABLE,
                'locked_by' => null,
                'locked_until' => null,
                'booking_id' => null,
                'updated_at' => now(),
            ]);
    }

    public function lockSeats(Show $show, array $seatIds, User $user): Collection
    {
        $seatIds = array_unique(array_map('intval', $seatIds));

        return DB::transaction(function () use ($show, $seatIds, $user) {
            $this->releaseExpiredLocks($show);

            $seats = $show->seats()
                ->whereIn('id', $seatIds)
                ->lockForUpdate()
                ->get();

            if ($seats->count() !== count($seatIds)) {
                throw ValidationException::withMessages([
                    'seats' => 'One or more selected seats do not exist for this show.',
                ]);
            }

            $invalidSeats = $seats->filter(function (ShowSeat $seat) use ($user) {
                if ($seat->status === ShowSeat::STATUS_BOOKED || $seat->status === ShowSeat::STATUS_BLOCKED) {
                    return true;
                }

                return $seat->status === ShowSeat::STATUS_LOCKED
                    && $seat->locked_by !== $user->id
                    && $seat->locked_until?->isFuture();
            });

            if ($invalidSeats->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'seats' => 'Some seats were just booked or locked by another user.',
                ]);
            }

            $show->seats()->whereIn('id', $seatIds)->update([
                'status' => ShowSeat::STATUS_LOCKED,
                'locked_by' => $user->id,
                'locked_until' => now()->addMinutes($show->seat_lock_minutes),
                'updated_at' => now(),
            ]);

            return $show->seats()->whereIn('id', $seatIds)->get();
        });
    }

    public function attachSeatsToBooking(Booking $booking, Collection $seats): void
    {
        $booking->show->seats()->whereIn('id', $seats->pluck('id'))->update([
            'booking_id' => $booking->id,
            'updated_at' => now(),
        ]);

        foreach ($seats as $seat) {
            BookingItem::updateOrCreate(
                [
                    'booking_id' => $booking->id,
                    'show_seat_id' => $seat->id,
                ],
                [
                    'seat_number' => $seat->seat_number,
                    'seat_type_name' => $seat->seatType?->name,
                    'unit_price' => $seat->price,
                    'status' => $booking->status,
                ]
            );
        }
    }

    public function markAsBooked(Booking $booking): void
    {
        $seatIds = $booking->items()->pluck('show_seat_id')->filter();

        $booking->show->seats()->whereIn('id', $seatIds)->update([
            'status' => ShowSeat::STATUS_BOOKED,
            'locked_by' => null,
            'locked_until' => null,
            'booking_id' => $booking->id,
            'booked_at' => now(),
            'updated_at' => now(),
        ]);

        $booking->items()->update(['status' => 'confirmed']);

        $booking->show->update([
            'available_seats' => $booking->show->seats()->where('status', ShowSeat::STATUS_AVAILABLE)->count(),
        ]);
    }

    public function releaseBookingSeats(Booking $booking): void
    {
        $seatIds = $booking->items()->pluck('show_seat_id')->filter();

        $booking->show->seats()->whereIn('id', $seatIds)->update([
            'status' => ShowSeat::STATUS_AVAILABLE,
            'locked_by' => null,
            'locked_until' => null,
            'booking_id' => null,
            'booked_at' => null,
            'updated_at' => now(),
        ]);

        $booking->items()->update(['status' => 'cancelled']);

        $booking->show->update([
            'available_seats' => $booking->show->seats()->where('status', ShowSeat::STATUS_AVAILABLE)->count(),
        ]);
    }

    public function syncPricingForShow(Show $show): void
    {
        $show->loadMissing('seats.seatType');

        foreach ($show->seats as $seat) {
            $updatedPrice = round((float) $show->price * (float) ($seat->seatType?->price_multiplier ?? 1), 2);

            $seat->update([
                'base_price' => $show->price,
                'price' => $seat->status === ShowSeat::STATUS_BOOKED ? $seat->price : $updatedPrice,
            ]);
        }
    }

    private function ensureDefaultSeatTypes(): Collection
    {
        $defaults = [
            ['name' => 'VIP', 'code' => 'VIP', 'color' => '#7c3aed', 'price_multiplier' => 2.2],
            ['name' => 'Gold', 'code' => 'GOLD', 'color' => '#ca8a04', 'price_multiplier' => 1.6],
            ['name' => 'Silver', 'code' => 'SILVER', 'color' => '#64748b', 'price_multiplier' => 1.2],
            ['name' => 'Normal', 'code' => 'NORMAL', 'color' => '#2563eb', 'price_multiplier' => 1],
        ];

        foreach ($defaults as $default) {
            SeatType::firstOrCreate(
                ['code' => $default['code']],
                $default + ['is_active' => true]
            );
        }

        return SeatType::query()->where('is_active', true)->get();
    }

    private function resolveSeatTypeCode(int $rowIndex, int $totalRows): string
    {
        $position = ($rowIndex + 1) / $totalRows;

        return match (true) {
            $position <= 0.2 => 'VIP',
            $position <= 0.45 => 'GOLD',
            $position <= 0.75 => 'SILVER',
            default => 'NORMAL',
        };
    }

    private function rowLabel(int $index): string
    {
        $label = '';
        $index++;

        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $label = chr(65 + $mod).$label;
            $index = (int) (($index - $mod) / 26);
            $index--;
        }

        return $label;
    }
}
