<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\SeatType;
use App\Models\Show;
use App\Models\Venue;
use App\Services\ActivityLogService;
use App\Services\SeatLayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowController extends Controller
{
    public function __construct(
        private readonly SeatLayoutService $seatLayoutService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $shows = Show::query()
            ->with(['event.eventCategory', 'venue.cityRecord'])
            ->when($request->integer('event_id'), fn ($query, $eventId) => $query->where('event_id', $eventId))
            ->when($request->string('status')->value(), fn ($query, $status) => $query->where('status', $status))
            ->when($request->integer('city_id'), fn ($query, $cityId) => $query->whereHas('venue', fn ($venue) => $venue->where('city_id', $cityId)))
            ->latest('show_date')
            ->latest('show_time')
            ->paginate(12)
            ->withQueryString();

        $events = Event::query()->orderBy('title')->get();

        return view('admin.shows.index', compact('shows', 'events'));
    }

    public function create(Event $event): View
    {
        $venues = Venue::orderBy('name')->get();

        return view('admin.shows.create', compact('event', 'venues'));
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $show = $event->shows()->create($this->validatedData($request));
        $this->syncTicketTypes($request, $show);

        if ($show->booking_mode === 'reserved_seating') {
            $this->seatLayoutService->generateForShow($show->load('venue'));
        }

        $this->activityLogService->log($request->user(), 'show.created', $show, 'Show created.');

        return redirect()->route('admin.shows.show', $show)->with('success', 'Show created successfully.');
    }

    public function show(Show $show): View
    {
        $show->load(['event.eventCategory', 'venue.cityRecord', 'seats.seatType', 'bookings.user']);
        $seatTypes = SeatType::query()->orderByDesc('price_multiplier')->get();

        return view('admin.shows.show', compact('show', 'seatTypes'));
    }

    public function edit(Show $show): View
    {
        $show->load('event');
        $venues = Venue::orderBy('name')->get();

        return view('admin.shows.edit', compact('show', 'venues'));
    }

    public function update(Request $request, Show $show): RedirectResponse
    {
        $show->update($this->validatedData($request));
        $this->syncTicketTypes($request, $show);

        if ($show->booking_mode !== 'reserved_seating') {
            $show->seats()->whereIn('status', ['available', 'blocked'])->delete();
        }

        $this->activityLogService->log($request->user(), 'show.updated', $show, 'Show updated.');

        return redirect()->route('admin.shows.show', $show)->with('success', 'Show updated successfully.');
    }

    public function destroy(Request $request, Show $show): RedirectResponse
    {
        $event = $show->event;
        $show->delete();

        $this->activityLogService->log($request->user(), 'show.deleted', $show, 'Show deleted.');

        return redirect()->route('admin.events.show', $event)->with('success', 'Show deleted successfully.');
    }

    public function regenerateSeats(Request $request, Show $show): RedirectResponse
    {
        if ($show->booking_mode !== 'reserved_seating') {
            return back()->with('error', 'Seat layout is only available for reserved seating shows.');
        }

        $this->seatLayoutService->generateForShow($show->load('venue'), true);
        $this->activityLogService->log($request->user(), 'show.seats_regenerated', $show, 'Seat layout regenerated.');

        return back()->with('success', 'Seat layout regenerated successfully.');
    }

    public function updateSeatPricing(Request $request, Show $show): RedirectResponse
    {
        $data = $request->validate([
            'seat_prices' => ['required', 'array'],
            'seat_prices.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        foreach ($data['seat_prices'] as $seatTypeId => $price) {
            if ($price === null || $price === '') {
                continue;
            }

            $show->seats()->where('seat_type_id', $seatTypeId)->update([
                'price' => $price,
                'updated_at' => now(),
            ]);
        }

        $this->activityLogService->log($request->user(), 'show.pricing_updated', $show, 'Seat pricing updated.');

        return back()->with('success', 'Seat pricing updated successfully.');
    }

    public function updateSeatStatus(Request $request, Show $show): RedirectResponse
    {
        $data = $request->validate([
            'seat_ids' => ['required', 'array', 'min:1'],
            'seat_ids.*' => ['integer', 'exists:show_seats,id'],
            'status' => ['required', 'in:available,blocked'],
        ]);

        $show->seats()
            ->whereIn('id', $data['seat_ids'])
            ->whereNotIn('status', ['booked', 'locked'])
            ->update([
                'status' => $data['status'],
                'updated_at' => now(),
            ]);

        $show->update([
            'available_seats' => $show->seats()->where('status', 'available')->count(),
        ]);

        $this->activityLogService->log($request->user(), 'show.seats_status_updated', $show, 'Seat availability updated.');

        return back()->with('success', 'Seat status updated successfully.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'venue_id' => ['required', 'exists:venues,id'],
            'show_date' => ['required', 'date'],
            'show_time' => ['required', 'date_format:H:i'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['required', 'in:INR,USD,EUR,GBP,SGD,AED,BRL'],
            'available_seats' => ['required', 'integer', 'min:1'],
            'sales_capacity' => ['nullable', 'integer', 'min:1'],
            'booking_mode' => ['required', 'in:reserved_seating,general_admission,tiered_tickets'],
            'status' => ['required', 'in:scheduled,cancelled,sold_out'],
            'booking_open_at' => ['nullable', 'date'],
            'booking_close_at' => ['nullable', 'date', 'after_or_equal:booking_open_at'],
            'seat_lock_minutes' => ['required', 'integer', 'min:1', 'max:30'],
        ]);
    }

    private function syncTicketTypes(Request $request, Show $show): void
    {
        if ($show->booking_mode !== 'tiered_tickets') {
            $show->ticketTypes()->delete();
            return;
        }

        $ticketTypes = collect($request->input('ticket_types', []))
            ->filter(fn ($ticketType) => filled($ticketType['name'] ?? null) && filled($ticketType['price'] ?? null))
            ->values();

        $show->ticketTypes()->delete();

        foreach ($ticketTypes as $ticketType) {
            $show->ticketTypes()->create([
                'name' => $ticketType['name'],
                'code' => $ticketType['code'] ?? null,
                'description' => $ticketType['description'] ?? null,
                'price' => $ticketType['price'],
                'capacity' => $ticketType['capacity'] ?? null,
                'is_active' => true,
            ]);
        }
    }
}
