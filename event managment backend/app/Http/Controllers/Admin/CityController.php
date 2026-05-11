<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Services\ActivityLogService;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CityController extends Controller
{
    public function __construct(
        private readonly SlugService $slugService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $cities = City::query()
            ->with('countryRecord')
            ->when($request->string('search')->value(), function ($query, $search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                });
            })
            ->when($request->integer('country_id'), fn ($query, $countryId) => $query->where('country_id', $countryId))
            ->withCount('venues')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $countries = Country::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.cities.index', compact('cities', 'countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $country = Country::findOrFail($data['country_id']);

        $city = City::create([
            ...$data,
            'slug' => $this->slugService->generate(City::class, $data['name']),
            'country' => $country->name,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->activityLogService->log($request->user(), 'city.created', $city, 'City created.');

        return back()->with('success', 'City created successfully.');
    }

    public function update(Request $request, City $city): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $country = Country::findOrFail($data['country_id']);

        $city->update([
            ...$data,
            'slug' => $this->slugService->generate(City::class, $data['name'], $city->id),
            'country' => $country->name,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->activityLogService->log($request->user(), 'city.updated', $city, 'City updated.');

        return back()->with('success', 'City updated successfully.');
    }

    public function destroy(Request $request, City $city): RedirectResponse
    {
        $city->delete();

        $this->activityLogService->log($request->user(), 'city.deleted', $city, 'City deleted.');

        return back()->with('success', 'City deleted successfully.');
    }
}
