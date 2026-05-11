<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\ActivityLogService;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function __construct(
        private readonly SlugService $slugService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $countries = Country::query()
            ->when($request->string('search')->value(), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->withCount('cities')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.countries.index', compact('countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'iso_code' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $country = Country::create([
            ...$data,
            'slug' => $this->slugService->generate(Country::class, $data['name']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->activityLogService->log($request->user(), 'country.created', $country, 'Country created.');

        return back()->with('success', 'Country created successfully.');
    }

    public function update(Request $request, Country $country): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'iso_code' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $country->update([
            ...$data,
            'slug' => $this->slugService->generate(Country::class, $data['name'], $country->id),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->activityLogService->log($request->user(), 'country.updated', $country, 'Country updated.');

        return back()->with('success', 'Country updated successfully.');
    }

    public function destroy(Request $request, Country $country): RedirectResponse
    {
        if ($country->cities()->exists()) {
            return back()->with('error', 'Country cannot be deleted while cities are assigned to it.');
        }

        $country->delete();

        $this->activityLogService->log($request->user(), 'country.deleted', $country, 'Country deleted.');

        return back()->with('success', 'Country deleted successfully.');
    }
}
