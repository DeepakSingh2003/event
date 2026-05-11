<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\City;
use App\Models\Event;
use App\Services\ActivityLogService;
use App\Services\BookingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $bookings = Booking::query()
            ->with(['user', 'event.eventCategory', 'show.venue.cityRecord', 'coupon'])
            ->when($request->string('status')->value(), fn ($query, $status) => $query->where('status', $status))
            ->when($request->string('payment_status')->value(), fn ($query, $status) => $query->where('payment_status', $status))
            ->when($request->integer('event_id'), fn ($query, $eventId) => $query->where('event_id', $eventId))
            ->when($request->integer('city_id'), fn ($query, $cityId) => $query->whereHas('show.venue', fn ($venue) => $venue->where('city_id', $cityId)))
            ->when($request->date('from'), fn ($query, $from) => $query->whereDate('booked_at', '>=', $from))
            ->when($request->date('to'), fn ($query, $to) => $query->whereDate('booked_at', '<=', $to))
            ->latest('booked_at')
            ->paginate(12);

        $cities = City::query()->orderBy('name')->get();
        $events = Event::query()->orderBy('title')->get();

        return view('admin.bookings.index', compact('bookings', 'cities', 'events'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['user', 'event.eventCategory', 'show.venue.cityRecord', 'items.showSeat.seatType', 'paymentLogs', 'refunds']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $bookings = Booking::query()
            ->with(['user', 'event', 'show.venue'])
            ->latest('booked_at')
            ->get();

        return response()->streamDownload(function () use ($bookings) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Reference', 'User', 'Event', 'Show', 'Seats', 'Status', 'Payment', 'Amount']);

            foreach ($bookings as $booking) {
                fputcsv($handle, [
                    $booking->booking_reference,
                    $booking->user?->name,
                    $booking->event?->title,
                    optional($booking->show?->show_date)->format('Y-m-d'),
                    $booking->seats,
                    $booking->status,
                    $booking->payment_status,
                    $booking->total_amount,
                ]);
            }

            fclose($handle);
        }, 'bookings.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportPdf(Request $request)
    {
        $bookings = Booking::query()->with(['user', 'event', 'show.venue'])->latest('booked_at')->get();

        return Pdf::loadView('pdf.bookings', compact('bookings'))->download('bookings.pdf');
    }

    public function confirm(Request $request, Booking $booking): RedirectResponse
    {
        $this->bookingService->confirmBooking($booking, [
            'payment_id' => $request->input('payment_id', $booking->payment_id),
            'payment_status' => $request->input('payment_status', 'paid'),
        ]);

        return back()->with('success', 'Booking confirmed successfully.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->bookingService->cancelBooking($booking, $request->input('reason'));

        return back()->with('success', 'Booking cancelled successfully.');
    }

    public function refund(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->bookingService->refundBooking($booking, (float) $data['amount'], $data['reason']);

        return back()->with('success', 'Refund processed successfully.');
    }

    public function ticket(Booking $booking)
    {
        abort_unless($booking->ticket_path && Storage::disk('public')->exists($booking->ticket_path), 404);

        return Storage::disk('public')->download($booking->ticket_path, $booking->booking_reference.'.pdf');
    }
}
