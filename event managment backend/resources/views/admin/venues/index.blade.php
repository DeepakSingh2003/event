<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">
                    Venues
                </p>
                <h2 class="text-3xl font-semibold text-slate-900">
                    Venue & City Management
                </h2>
            </div>

            <a href="{{ route('admin.venues.create') }}" class="panel-btn">
                Create Venue
            </a>

        </div>
    </x-slot>

    <div class="panel-card p-6">
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <input type="text" name="search" value="{{ request('search') }}" class="panel-input" placeholder="Search venue">
            <select name="city_id" class="panel-select">
                <option value="">All cities</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>{{ $city->name }}</option>
                @endforeach
            </select>
            <button class="panel-btn" type="submit">Filter</button>
        </form>
    </div>

    <div class="panel-card mt-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="panel-table">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Venue</th>
                        <th class="px-6 py-4">City</th>
                        <th class="px-6 py-4">Capacity</th>
                        <th class="px-6 py-4">Layout</th>
                        <th class="px-6 py-4">Shows</th>
                        <th class="px-6 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($venues as $venue)
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-semibold">{{ $venue->name }}</p>
                                <p class="text-sm text-slate-500">{{ $venue->address }}</p>
                            </td>
                            <td class="px-6 py-4">{{ $venue->cityRecord?->name ?? $venue->city }}</td>
                            <td class="px-6 py-4">{{ $venue->total_seats }}</td>
                            <td class="px-6 py-4">{{ $venue->row_count }} x {{ $venue->column_count }}</td>
                            <td class="px-6 py-4">{{ $venue->shows_count }}</td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.venues.show', $venue) }}" class="panel-btn-secondary">View</a>
                                    <a href="{{ route('admin.venues.edit', $venue) }}" class="panel-btn-secondary">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $venues->links() }}</div>
    </div>
</x-app-layout>
