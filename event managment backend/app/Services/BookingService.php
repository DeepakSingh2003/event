<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Refund;
use App\Models\Show;
use App\Models\ShowTicketType;
use App\Models\User;
use App\Notifications\AdminNewBookingNotification;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly SeatLayoutService $seatLayoutService,
        private readonly PaymentGatewayService $paymentGatewayService,
        private readonly TicketService $ticketService,
        private readonly SettingsService $settingsService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function createBooking(
        User $user,
        Show $show,
        array $seatIds,
        ?string $couponCode = null,
        string $gateway = 'manual',
        bool $confirmImmediately = false,
        ?string $paymentProofPath = null
    ): array {
        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'user' => 'Blocked users cannot place bookings.',
            ]);
        }

        $this->seatLayoutService->generateForShow($show);
        $lockedSeats = $this->seatLayoutService->lockSeats($show, $seatIds, $user)->loadMissing('seatType');
        $subtotal = (float) $lockedSeats->sum('price');
        $coupon = $couponCode ? Coupon::query()->where('code', strtoupper($couponCode))->first() : null;
        $discount = $this->resolveDiscount($coupon, $subtotal);
        $taxRate = (float) $this->settingsService->get('localization.tax_percentage', 0);
        $taxAmount = round(max($subtotal - $discount, 0) * ($taxRate / 100), 2);
        $total = round(max($subtotal - $discount, 0) + $taxAmount, 2);

        $booking = DB::transaction(function () use ($user, $show, $lockedSeats, $coupon, $subtotal, $discount, $taxAmount, $total, $gateway, $paymentProofPath) {
            $booking = Booking::create([
                'booking_reference' => 'BMS-'.Str::upper(Str::random(10)),
                'user_id' => $user->id,
                'event_id' => $show->event_id,
                'show_id' => $show->id,
                'coupon_id' => $coupon?->id,
                'seats' => $lockedSeats->count(),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $taxAmount,
                'total_amount' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_gateway' => $gateway,
                'payment_proof_path' => $paymentProofPath,
                'booked_at' => now(),
                'expires_at' => now()->addMinutes($show->seat_lock_minutes),
                'qr_token' => (string) Str::uuid(),
            ]);

            $this->seatLayoutService->attachSeatsToBooking($booking, $lockedSeats);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            return $booking;
        });

        $payment = $this->paymentGatewayService->createCheckout($booking, $gateway);

        if ($gateway === 'manual') {
            $booking->seats()->whereIn('id', $lockedSeats->pluck('id'))->update([
                'status' => 'blocked',
                'locked_by' => null,
                'locked_until' => null,
                'booking_id' => $booking->id,
                'updated_at' => now(),
            ]);

            $show->update([
                'available_seats' => $show->seats()->where('status', 'available')->count(),
            ]);
        }

        if ($confirmImmediately) {
            $booking = $this->confirmBooking($booking, [
                'payment_id' => $payment['payment_reference'] ?? null,
                'payment_status' => 'paid',
            ]);
        }

        $this->notifyAdmins($booking);
        $this->activityLogService->log($user, 'booking.created', $booking, 'Booking initiated.', [
            'gateway' => $gateway,
            'seats' => $lockedSeats->pluck('seat_number')->all(),
        ]);

        return [
            'booking' => $booking->fresh(['items', 'show.venue', 'event', 'coupon']),
            'payment' => $payment,
        ];
    }

    public function createGeneralAdmissionBooking(
        User $user,
        Show $show,
        int $quantity,
        ?string $couponCode = null,
        string $gateway = 'manual',
        ?string $paymentProofPath = null
    ): array {
        $capacity = (int) ($show->sales_capacity ?: $show->available_seats);
        $sold = (int) $show->bookings()->whereIn('status', ['pending', 'confirmed'])->sum('seats');

        if ($quantity < 1 || ($sold + $quantity) > $capacity) {
            throw ValidationException::withMessages([
                'quantity' => 'Not enough tickets are available for this show.',
            ]);
        }

        return $this->createQuantityBooking($user, $show, 'General Admission', null, (float) $show->price, $quantity, $couponCode, $gateway, $paymentProofPath);
    }

    public function createTicketTypeBooking(
        User $user,
        Show $show,
        int $ticketTypeId,
        int $quantity,
        ?string $couponCode = null,
        string $gateway = 'manual',
        ?string $paymentProofPath = null
    ): array {
        $ticketType = ShowTicketType::query()
            ->where('show_id', $show->id)
            ->where('is_active', true)
            ->findOrFail($ticketTypeId);

        if ($ticketType->capacity !== null && ($ticketType->sold_count + $quantity) > $ticketType->capacity) {
            throw ValidationException::withMessages([
                'quantity' => 'Not enough tickets are available for this ticket type.',
            ]);
        }

        return $this->createQuantityBooking($user, $show, $ticketType->name, $ticketType, (float) $ticketType->price, $quantity, $couponCode, $gateway, $paymentProofPath);
    }

    public function confirmBooking(Booking $booking, array $paymentData = []): Booking
    {
        DB::transaction(function () use ($booking, $paymentData) {
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => $paymentData['payment_status'] ?? 'paid',
                'payment_id' => $paymentData['payment_id'] ?? $booking->payment_id,
                'confirmed_at' => now(),
            ]);

            if (($booking->show->booking_mode ?? 'reserved_seating') === 'reserved_seating') {
                $this->seatLayoutService->markAsBooked($booking);
            } else {
                $booking->items()->update(['status' => 'confirmed']);
            }

            $path = $this->ticketService->generatePdf($booking->fresh(['items', 'show.venue', 'event.eventCategory', 'user']));
            $booking->update(['ticket_path' => $path]);
        });

        $booking->refresh()->loadMissing(['user', 'items', 'show.venue', 'event.eventCategory']);

        $booking->user?->notify(new BookingConfirmedNotification($booking));

        $this->activityLogService->log($booking->user, 'booking.confirmed', $booking, 'Booking confirmed successfully.');

        return $booking;
    }

    public function cancelBooking(Booking $booking, ?string $reason = null): Booking
    {
        DB::transaction(function () use ($booking, $reason) {
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'notes' => $reason ?: $booking->notes,
            ]);

            if (($booking->show->booking_mode ?? 'reserved_seating') === 'reserved_seating') {
                $this->seatLayoutService->releaseBookingSeats($booking);
            } else {
                foreach ($booking->items as $item) {
                    if ($item->show_ticket_type_id) {
                        ShowTicketType::query()
                            ->whereKey($item->show_ticket_type_id)
                            ->decrement('sold_count', (int) ($item->quantity ?? 1));
                    }
                }

                $booking->show->increment('available_seats', (int) $booking->seats);
            }
        });

        $this->activityLogService->log($booking->user, 'booking.cancelled', $booking, 'Booking cancelled.', [
            'reason' => $reason,
        ]);

        return $booking->fresh(['items', 'show.venue', 'event']);
    }

    public function refundBooking(Booking $booking, float $amount, string $reason): Refund
    {
        if ($amount <= 0 || $amount > (float) $booking->total_amount) {
            throw ValidationException::withMessages([
                'amount' => 'Refund amount must be between 0 and the booking total.',
            ]);
        }

        $refund = Refund::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'amount' => $amount,
            'status' => 'processed',
            'reason' => $reason,
            'gateway_reference' => 'REF-'.Str::upper(Str::random(10)),
            'processed_at' => now(),
        ]);

        $booking->update([
            'refund_amount' => $booking->refund_amount + $amount,
            'refund_status' => $amount >= (float) $booking->total_amount ? 'full' : 'partial',
            'payment_status' => 'refunded',
        ]);

        $this->activityLogService->log($booking->user, 'booking.refunded', $booking, 'Refund processed.', [
            'amount' => $amount,
            'reason' => $reason,
        ]);

        return $refund;
    }

    private function resolveDiscount(?Coupon $coupon, float $subtotal): float
    {
        if (! $coupon) {
            return 0;
        }

        if (! $coupon->isValidForAmount($subtotal)) {
            throw ValidationException::withMessages([
                'coupon' => 'This coupon is not valid for the selected booking.',
            ]);
        }

        $discount = $coupon->type === 'percentage'
            ? round($subtotal * ((float) $coupon->value / 100), 2)
            : (float) $coupon->value;

        if ($coupon->max_discount !== null) {
            $discount = min($discount, (float) $coupon->max_discount);
        }

        return min($discount, $subtotal);
    }

    private function createQuantityBooking(
        User $user,
        Show $show,
        string $ticketName,
        ?ShowTicketType $ticketType,
        float $unitPrice,
        int $quantity,
        ?string $couponCode,
        string $gateway,
        ?string $paymentProofPath = null
    ): array {
        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'user' => 'Blocked users cannot place bookings.',
            ]);
        }

        $subtotal = round($unitPrice * $quantity, 2);
        $coupon = $couponCode ? Coupon::query()->where('code', strtoupper($couponCode))->first() : null;
        $discount = $this->resolveDiscount($coupon, $subtotal);
        $taxRate = (float) $this->settingsService->get('localization.tax_percentage', 0);
        $taxAmount = round(max($subtotal - $discount, 0) * ($taxRate / 100), 2);
        $total = round(max($subtotal - $discount, 0) + $taxAmount, 2);

        $booking = DB::transaction(function () use ($user, $show, $ticketName, $ticketType, $unitPrice, $quantity, $coupon, $subtotal, $discount, $taxAmount, $total, $gateway, $paymentProofPath) {
            if ($ticketType) {
                $freshTicketType = ShowTicketType::query()->lockForUpdate()->findOrFail($ticketType->id);
                if ($freshTicketType->capacity !== null && ($freshTicketType->sold_count + $quantity) > $freshTicketType->capacity) {
                    throw ValidationException::withMessages(['quantity' => 'Not enough tickets are available for this ticket type.']);
                }
                $freshTicketType->increment('sold_count', $quantity);
            }

            $booking = Booking::create([
                'booking_reference' => 'BMS-'.Str::upper(Str::random(10)),
                'user_id' => $user->id,
                'event_id' => $show->event_id,
                'show_id' => $show->id,
                'coupon_id' => $coupon?->id,
                'seats' => $quantity,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $taxAmount,
                'total_amount' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_gateway' => $gateway,
                'payment_proof_path' => $paymentProofPath,
                'booked_at' => now(),
                'expires_at' => now()->addMinutes($show->seat_lock_minutes),
                'qr_token' => (string) Str::uuid(),
            ]);

            $booking->items()->create([
                'show_ticket_type_id' => $ticketType?->id,
                'seat_number' => $ticketName,
                'seat_type_name' => $ticketName,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'status' => 'pending',
            ]);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            return $booking;
        });

        $payment = $this->paymentGatewayService->createCheckout($booking, $gateway);

        if ($gateway === 'manual') {
            $show->update([
                'available_seats' => max(0, (int) $show->available_seats - $quantity),
            ]);
        }

        $this->notifyAdmins($booking);

        return [
            'booking' => $booking->fresh(['items', 'show.venue', 'event', 'coupon']),
            'payment' => $payment,
        ];
    }

    private function notifyAdmins(Booking $booking): void
    {
        User::query()
            ->whereIn('role', ['admin', 'manager'])
            ->get()
            ->each(fn (User $recipient) => $recipient->notify(new AdminNewBookingNotification($booking)));
    }
}
