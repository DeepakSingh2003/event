<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CountryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $countries = Country::query()
            ->select('countries.*')
            ->selectSub(function ($query) {
                $query->from('shows')
                    ->join('venues', 'venues.id', '=', 'shows.venue_id')
                    ->join('cities', 'cities.id', '=', 'venues.city_id')
                    ->whereColumn('cities.country_id', 'countries.id')
                    ->where('cities.is_active', true)
                    ->selectRaw('COUNT(DISTINCT shows.event_id)');
            }, 'events_count')
            ->where('is_active', true)
            ->whereHas('cities', fn ($query) => $query->where('is_active', true))
            ->when($request->string('search')->value(), function ($query, $search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('iso_code', 'like', "%{$search}%");
                });
            })
            ->withCount(['cities as cities_count' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return CountryResource::collection($countries);
    }
}
