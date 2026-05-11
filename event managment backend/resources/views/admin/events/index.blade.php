<x-app-layout>
   <x-slot name="header">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">
                Events
            </p>
            <h2 class="text-3xl font-semibold text-slate-900">
                Event Ticket Listings
            </h2>
        </div>

        <a href="{{ route('admin.events.create') }}" class="panel-btn">
            Add Event Listing
        </a>
    </div>
</x-slot>

    <div class="panel-card p-6">
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <input type="text" name="search" value="{{ request('search') }}" class="panel-input" placeholder="Search event title or slug">
            <select name="category_id" class="panel-select">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="publication_status" class="panel-select">
                <option value="">All statuses</option>
                @foreach (['draft', 'published', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('publication_status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <div class="flex gap-3">
                <select name="featured" class="panel-select">
                    <option value="">Featured or not</option>
                    <option value="1" @selected(request('featured') === '1')>Featured only</option>
                    <option value="0" @selected(request('featured') === '0')>Non featured</option>
                </select>
                <button class="panel-btn" type="submit">Filter</button>
            </div>
        </form>
    </div>

    <div class="panel-card mt-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="panel-table">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Event</th>
                        <th class="px-6 py-4">Market</th>
                        <th class="px-6 py-4">Primary Date</th>
                        <th class="px-6 py-4">Base Price</th>
                        <th class="px-6 py-4">Orders</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($events as $event)
                        @php $listing = $event->primaryShow; @endphp
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    @if ($event->poster_image)
                                        <img src="{{ asset('storage/'.$event->poster_image) }}" alt="" class="h-14 w-14 rounded-2xl object-cover">
                                    @endif
                                    <div>
                                        <p class="font-semibold">{{ $event->title }}</p>
                                        <p class="text-sm text-slate-500">{{ $event->slug }}</p>
                                        <p class="mt-1 text-xs uppercase tracking-[0.2em] text-slate-400">{{ $event->shows_count }} ticket date{{ $event->shows_count === 1 ? '' : 's' }}</p>
                                        @if ($event->is_featured)
                                            <span class="panel-badge mt-2 bg-amber-100 text-amber-700">Featured</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($listing)
                                    <p class="font-semibold">{{ $listing->venue->name }}</p>
                                    <p class="text-sm text-slate-500">{{ $listing->venue->cityRecord?->name ?? $listing->venue->city }}, {{ $listing->venue->cityRecord?->country }}</p>
                                @else
                                    <span class="text-sm text-slate-500">Not configured</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($listing)
                                    <p class="font-semibold">{{ $listing->show_date->format('d M Y') }}</p>
                                    <p class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($listing->show_time)->format('h:i A') }}</p>
                                @else
                                    <span class="text-sm text-slate-500">No date yet</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-semibold">{{ $listing ? \App\Support\Currency::inr($listing->price, 0) : '-' }}</td>
                            <td class="px-6 py-4">
                                <p class="font-semibold">{{ $event->bookings_count }} bookings</p>
                                <p class="text-sm text-slate-500">{{ $listing?->available_seats ?? 0 }} seats left</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="panel-badge {{ $event->publication_status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($event->publication_status === 'cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ $event->publication_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('admin.events.show', $event) }}" class="panel-btn-secondary">View</a>
                                    <a href="{{ route('admin.events.edit', $event) }}" class="panel-btn-secondary">Edit</a>
                                    <form action="{{ route('admin.events.destroy', $event) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="panel-btn-secondary">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">
            {{ $events->links() }}
        </div>
    </div>
</x-app-layout>
