<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Venue;
use App\Services\ActivityLogService;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function __construct(
        private readonly SlugService $slugService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function index(): View
    {
        $venues = Venue::query()
            ->with(['cityRecord.countryRecord'])
            ->withCount('shows')
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when(request('city_id'), fn ($query, $cityId) => $query->where('city_id', $cityId))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $cities = City::query()->with('countryRecord')->orderBy('name')->get();

        return view('admin.venues.index', compact('venues', 'cities'));
    }

    public function create(): View
    {
        $cities = City::query()->with('countryRecord')->where('is_active', true)->orderBy('name')->get();

        return view('admin.venues.create', compact('cities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->slugService->generate(Venue::class, $data['slug'] ?: $data['name']);
        $data['city'] = City::find($data['city_id'])?->name ?? ($data['city'] ?? null);
        $data['layout_image'] = $this->storeLayoutImage($request);

        $venue = Venue::create($data);

        $this->activityLogService->log($request->user(), 'venue.created', $venue, 'Venue created.');

        return redirect()->route('admin.venues.index')->with('success', 'Venue created successfully.');
    }

    public function show(Venue $venue): View
    {
        $venue->load(['cityRecord.countryRecord', 'shows.event']);

        return view('admin.venues.show', compact('venue'));
    }

    public function edit(Venue $venue): View
    {
        $cities = City::query()->with('countryRecord')->where('is_active', true)->orderBy('name')->get();

        return view('admin.venues.edit', compact('venue', 'cities'));
    }

    public function update(Request $request, Venue $venue): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->slugService->generate(Venue::class, $data['slug'] ?: $data['name'], $venue->id);
        $data['city'] = City::find($data['city_id'])?->name ?? ($data['city'] ?? null);
        $data['layout_image'] = $this->storeLayoutImage($request) ?? $venue->layout_image;

        if ($request->boolean('remove_layout_image') && $venue->layout_image) {
            Storage::disk('public')->delete($venue->layout_image);
            $data['layout_image'] = null;
        }

        $venue->update($data);

        $this->activityLogService->log($request->user(), 'venue.updated', $venue, 'Venue updated.');

        return redirect()->route('admin.venues.index')->with('success', 'Venue updated successfully.');
    }

    public function destroy(Request $request, Venue $venue): RedirectResponse
    {
        if ($venue->shows()->exists()) {
            return redirect()->route('admin.venues.index')->with('error', 'Venue cannot be deleted while shows are assigned to it.');
        }

        $venue->delete();

        $this->activityLogService->log($request->user(), 'venue.deleted', $venue, 'Venue deleted.');

        return redirect()->route('admin.venues.index')->with('success', 'Venue deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:150'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'slug' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'total_seats' => ['required', 'integer', 'min:1'],
            'row_count' => ['required', 'integer', 'min:1'],
            'column_count' => ['required', 'integer', 'min:1'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'map_url' => ['nullable', 'url'],
            'layout_image' => ['nullable', 'image', 'max:4096'],
            'layout_label' => ['nullable', 'string', 'max:80'],
            'layout_label_position' => ['required', 'in:top,bottom,hidden'],
            'remove_layout_image' => ['nullable', 'boolean'],
        ]);

        $data['layout_label'] = ($data['layout_label'] ?? null) ?: 'SCREEN';

        if (($data['row_count'] * $data['column_count']) > $data['total_seats']) {
            abort(422, 'Venue total seats must be equal to or greater than row x column capacity.');
        }

        return $data;
    }

    private function storeLayoutImage(Request $request): ?string
    {
        if (! $request->hasFile('layout_image')) {
            return null;
        }

        return $request->file('layout_image')->store('venue-layouts', 'public');
    }
}
