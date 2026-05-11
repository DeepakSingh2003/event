<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Ticket Dates</p>
            <h2 class="text-3xl font-semibold text-slate-900">Ticket Dates & Seat Maps</h2>
        </div>
    </x-slot>

    <div class="panel-card p-6">
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <select name="event_id" class="panel-select">
                <option value="">All events</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" @selected((string) request('event_id') === (string) $event->id)>{{ $event->title }}</option>
                @endforeach
            </select>
            <select name="status" class="panel-select">
                <option value="">All statuses</option>
                @foreach (['scheduled', 'cancelled', 'sold_out'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
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
                        <th class="px-6 py-4">Event</th>
                        <th class="px-6 py-4">Market</th>
                        <th class="px-6 py-4">Date & Time</th>
                        <th class="px-6 py-4">Base Price</th>
                        <th class="px-6 py-4">Seats</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($shows as $show)
                        <tr>
                            <td class="px-6 py-4 font-semibold">{{ $show->event->title }}</td>
                            <td class="px-6 py-4">
                                {{ $show->venue->name }}<br>
                                <span class="text-sm text-slate-500">{{ $show->venue->cityRecord?->name ?? $show->venue->city }}, {{ $show->venue->cityRecord?->country }}</span>
                            </td>
                            <td class="px-6 py-4">
                                {{ $show->show_date->format('d M Y') }}<br>
                                <span class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($show->show_time)->format('h:i A') }}</span>
                            </td>
                            <td class="px-6 py-4 font-semibold">{{ \App\Support\Currency::inr($show->price, 0) }}</td>
                            <td class="px-6 py-4">{{ $show->available_seats }}</td>
                            <td class="px-6 py-4"><span class="panel-badge bg-slate-100 text-slate-700">{{ $show->status }}</span></td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.shows.show', $show) }}" class="panel-btn-secondary">Seat Map</a>
                                    <a href="{{ route('admin.shows.edit', $show) }}" class="panel-btn-secondary">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $shows->links() }}</div>
    </div>
</x-app-layout>
