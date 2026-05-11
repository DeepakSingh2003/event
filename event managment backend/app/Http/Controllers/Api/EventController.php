<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $events = Event::query()
            ->with(['eventCategory', 'tags', 'galleryImages', 'timelines', 'primaryShow.venue.cityRecord.countryRecord', 'primaryShow.ticketTypes', 'shows.venue.cityRecord.countryRecord', 'shows.ticketTypes'])
            ->where('publication_status', 'published')
            ->when($request->string('search')->value(), function ($query, $search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->integer('category_id'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($request->filled('featured'), fn ($query) => $query->where('is_featured', $request->boolean('featured')))
            ->when($request->integer('city_id'), fn ($query, $cityId) => $query->whereHas('shows.venue', fn ($venue) => $venue->where('city_id', $cityId)))
            ->when($request->integer('country_id'), fn ($query, $countryId) => $query->whereHas('shows.venue.cityRecord', fn ($city) => $city->where('country_id', $countryId)))
            ->when($request->string('country')->value(), fn ($query, $country) => $query->whereHas('shows.venue.cityRecord', fn ($city) => $city->where('country', $country)))
            ->when($request->boolean('international'), fn ($query) => $query->whereHas('shows.venue.cityRecord', fn ($city) => $city->where('country', '!=', 'India')))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return EventResource::collection($events);
    }

    public function show(string $event): EventResource
    {
        $event = Event::query()
            ->where('id', $event)
            ->orWhere('slug', $event)
            ->firstOrFail();

        $event->load(['eventCategory', 'tags', 'galleryImages', 'timelines', 'primaryShow.venue.cityRecord.countryRecord', 'primaryShow.ticketTypes', 'shows.venue.cityRecord.countryRecord', 'shows.ticketTypes']);

        return new EventResource($event);
    }
}
