<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Show;
use App\Services\BookingService;
use App\Services\SettingsService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly SettingsService $settingsService,
        private readonly TicketService $ticketService,
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['event', 'show.venue', 'items'])
            ->latest('booked_at')
            ->paginate(10);

        return BookingResource::collection($bookings);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'show_id' => ['required', 'exists:shows,id'],
            'seat_ids' => ['nullable', 'array', 'min:1'],
            'seat_ids.*' => ['integer', 'exists:show_seats,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'ticket_type_id' => ['nullable', 'integer', 'exists:show_ticket_types,id'],
            'coupon_code' => ['nullable', 'string'],
            'gateway' => ['nullable', 'in:manual,stripe,razorpay'],
            'payment_proof' => ['nullable', 'image', 'max:4096'],
        ]);

        $show = Show::query()->with(['venue', 'event', 'ticketTypes'])->findOrFail($data['show_id']);
        $defaultGateway = (string) $this->settingsService->get('payment.default_gateway', 'manual');
        $enabledGateways = $this->settingsService->get('payment.enabled_gateways', [$defaultGateway]);
        $enabledGateways = is_array($enabledGateways) ? $enabledGateways : [$defaultGateway];
        $enabledGateways = array_values(array_intersect($enabledGateways, ['manual', 'stripe', 'razorpay']));
        $gateway = $data['gateway'] ?? $defaultGateway;

        abort_unless(in_array($gateway, $enabledGateways, true), 422, 'Selected payment method is not available.');

        $paymentProofPath = null;

        if ($gateway === 'manual') {
            $request->validate([
                'payment_proof' => ['required', 'image', 'max:4096'],
            ]);

            $paymentProofPath = $request->file('payment_proof')->store('payment-proofs', 'public');
        }

        $result = match ($show->booking_mode ?? 'reserved_seating') {
            'general_admission' => $this->bookingService->createGeneralAdmissionBooking(
                $request->user(),
                $show,
                (int) ($data['quantity'] ?? 1),
                $data['coupon_code'] ?? null,
                $gateway,
                $paymentProofPath
            ),
            'tiered_tickets' => $this->bookingService->createTicketTypeBooking(
                $request->user(),
                $show,
                (int) ($data['ticket_type_id'] ?? 0),
                (int) ($data['quantity'] ?? 1),
                $data['coupon_code'] ?? null,
                $gateway,
                $paymentProofPath
            ),
            default => $this->bookingService->createBooking(
                $request->user(),
                $show,
                $data['seat_ids'] ?? [],
                $data['coupon_code'] ?? null,
                $gateway,
                false,
                $paymentProofPath
            ),
        };

        return response()->json([
            'booking' => new BookingResource($result['booking']->load(['show.venue', 'event', 'items'])),
            'payment' => $result['payment'],
        ], 201);
    }

    public function show(Request $request, Booking $booking): BookingResource
    {
        abort_unless($booking->user_id === $request->user()->id || $request->user()->canAccessAdmin(), 403);
        $booking->load(['event', 'show.venue', 'items']);

        return new BookingResource($booking);
    }

    public function ticket(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id || $request->user()->canAccessAdmin(), 403);
        abort_unless($booking->status === 'confirmed', 404);

        if (
            ! $booking->ticket_path ||
            ! str_ends_with(strtolower($booking->ticket_path), '.pdf') ||
            ! Storage::disk('public')->exists($booking->ticket_path)
        ) {
            $path = $this->ticketService->generatePdf(
                $booking->loadMissing(['user', 'event.eventCategory', 'show.venue', 'items'])
            );
            $booking->update(['ticket_path' => $path]);
        }

        return Storage::disk('public')->download(
            $booking->ticket_path,
            $booking->booking_reference.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function confirm(Request $request, Booking $booking): BookingResource
    {
        abort_unless($booking->user_id === $request->user()->id || $request->user()->canAccessAdmin(), 403);

        $booking = $this->bookingService->confirmBooking($booking, [
            'payment_id' => $request->input('payment_id'),
            'payment_status' => $request->input('payment_status', 'paid'),
        ]);

        return new BookingResource($booking->load(['event', 'show.venue', 'items']));
    }

    public function cancel(Request $request, Booking $booking): BookingResource
    {
        abort_unless($booking->user_id === $request->user()->id || $request->user()->canAccessAdmin(), 403);

        $booking = $this->bookingService->cancelBooking($booking, $request->input('reason'));

        return new BookingResource($booking->load(['event', 'show.venue', 'items']));
    }
}
