<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeatResource;
use App\Http\Resources\ShowResource;
use App\Models\Show;
use App\Services\SeatLayoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShowController extends Controller
{
    public function __construct(private readonly SeatLayoutService $seatLayoutService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $shows = Show::query()
            ->with(['event.eventCategory', 'venue.cityRecord.countryRecord', 'ticketTypes' => fn ($query) => $query->where('is_active', true)])
            ->when($request->integer('event_id'), fn ($query, $eventId) => $query->where('event_id', $eventId))
            ->when($request->integer('city_id'), fn ($query, $cityId) => $query->whereHas('venue', fn ($venue) => $venue->where('city_id', $cityId)))
            ->when($request->integer('venue_id'), fn ($query, $venueId) => $query->where('venue_id', $venueId))
            ->when($request->integer('country_id'), fn ($query, $countryId) => $query->whereHas('venue.cityRecord', fn ($city) => $city->where('country_id', $countryId)))
            ->when($request->string('country')->value(), fn ($query, $country) => $query->whereHas('venue.cityRecord', fn ($city) => $city->where('country', $country)))
            ->when($request->date('date'), fn ($query, $date) => $query->whereDate('show_date', $date))
            ->where('status', 'scheduled')
            ->orderBy('show_date')
            ->orderBy('show_time')
            ->paginate(12)
            ->withQueryString();

        return ShowResource::collection($shows);
    }

    public function show(Show $show): ShowResource
    {
        $show->load(['event.eventCategory', 'venue.cityRecord.countryRecord', 'ticketTypes' => fn ($query) => $query->where('is_active', true), 'seats.seatType']);
        $this->seatLayoutService->releaseExpiredLocks($show);

        return new ShowResource($show);
    }

    public function seats(Show $show): AnonymousResourceCollection
    {
        $this->seatLayoutService->releaseExpiredLocks($show);
        $show->load('seats.seatType');

        return SeatResource::collection($show->seats);
    }

    public function lock(Request $request, Show $show): AnonymousResourceCollection
    {
        $data = $request->validate([
            'seat_ids' => ['required', 'array', 'min:1'],
            'seat_ids.*' => ['integer', 'exists:show_seats,id'],
        ]);

        $seats = $this->seatLayoutService
            ->lockSeats($show, $data['seat_ids'], $request->user())
            ->load('seatType');

        return SeatResource::collection($seats);
    }
}
