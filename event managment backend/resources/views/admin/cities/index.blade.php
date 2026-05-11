<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Cities</p>
            <h2 class="text-3xl font-semibold text-slate-900">City Management</h2>
        </div>
    </x-slot>

    <div class="panel-card p-6">
        <form action="{{ route('admin.cities.store') }}" method="POST" class="grid gap-4 md:grid-cols-4">
            @csrf
            <input type="text" name="name" class="panel-input" placeholder="City name" required>
            <input type="text" name="state" class="panel-input" placeholder="State">
            <select name="country_id" class="panel-select" required>
                <option value="">Select country</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                @endforeach
            </select>
            <button class="panel-btn" type="submit">Add City</button>
        </form>
    </div>

    <div class="panel-card mt-6 overflow-hidden">
        <table class="panel-table">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-4">City</th>
                    <th class="px-6 py-4">State</th>
                    <th class="px-6 py-4">Country</th>
                    <th class="px-6 py-4">Venues</th>
                    <th class="px-6 py-4">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($cities as $city)
                    <tr>
                        <td class="px-6 py-4 font-semibold">{{ $city->name }}</td>
                        <td class="px-6 py-4">{{ $city->state }}</td>
                        <td class="px-6 py-4">{{ $city->countryRecord?->name ?? $city->country }}</td>
                        <td class="px-6 py-4">{{ $city->venues_count }}</td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.cities.destroy', $city) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="panel-btn-secondary" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-6 py-4">{{ $cities->links() }}</div>
    </div>
</x-app-layout>
