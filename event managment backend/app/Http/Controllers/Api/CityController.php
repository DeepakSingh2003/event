<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Models\Show;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $cities = City::query()
            ->select('cities.*')
            ->selectSub(function ($query) {
                $query->from('shows')
                    ->join('venues', 'venues.id', '=', 'shows.venue_id')
                    ->whereColumn('venues.city_id', 'cities.id')
                    ->selectRaw('COUNT(DISTINCT shows.event_id)');
            }, 'events_count')
            ->with('countryRecord')
            ->where('is_active', true)
            ->when($request->integer('country_id'), fn ($query, $countryId) => $query->where('country_id', $countryId))
            ->when($request->string('country')->value(), fn ($query, $country) => $query->where('country', $country))
            ->when($request->string('search')->value(), function ($query, $search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%");
                });
            })
            ->withCount('venues')
            ->orderBy('country')
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return CityResource::collection($cities);
    }

    public function show(string $city): CityResource
    {
        $city = City::query()
            ->where('id', $city)
            ->orWhere('slug', $city)
            ->firstOrFail();

        $city->load(['countryRecord'])->loadCount('venues');
        $city->setAttribute(
            'events_count',
            Show::query()
                ->join('venues', 'venues.id', '=', 'shows.venue_id')
                ->where('venues.city_id', $city->id)
                ->distinct('shows.event_id')
                ->count('shows.event_id')
        );

        return new CityResource($city);
    }
}
