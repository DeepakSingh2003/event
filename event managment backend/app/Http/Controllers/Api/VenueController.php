<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VenueResource;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VenueController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $venues = Venue::query()
            ->with(['cityRecord.countryRecord'])
            ->withCount('shows')
            ->when($request->integer('city_id'), fn ($query, $cityId) => $query->where('city_id', $cityId))
            ->when($request->integer('country_id'), fn ($query, $countryId) => $query->whereHas('cityRecord', fn ($city) => $city->where('country_id', $countryId)))
            ->when($request->string('country')->value(), fn ($query, $country) => $query->whereHas('cityRecord', fn ($city) => $city->where('country', $country)))
            ->when($request->string('search')->value(), function ($query, $search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return VenueResource::collection($venues);
    }

    public function show(Venue $venue): VenueResource
    {
        $venue->load(['cityRecord.countryRecord'])->loadCount('shows');

        return new VenueResource($venue);
    }
}
