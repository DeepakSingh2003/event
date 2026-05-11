<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <!-- Left Side -->
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">
                Bookings
            </p>
            <h2 class="text-3xl font-semibold text-slate-900">
                Advanced Booking Management
            </h2>
        </div>

        <!-- Right Side Buttons -->
        <div class="flex gap-3">
            <a href="{{ route('admin.bookings.export.csv') }}" class="panel-btn-secondary">
                Export CSV
            </a>
            <a href="{{ route('admin.bookings.export.pdf') }}" class="panel-btn">
                Export PDF
            </a>
        </div>

    </div>
</x-slot>

    <div class="panel-card p-6">
        <form method="GET" class="grid gap-4 md:grid-cols-5">
            <select name="event_id" class="panel-select">
                <option value="">All events</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" @selected((string) request('event_id') === (string) $event->id)>{{ $event->title }}</option>
                @endforeach
            </select>
            <select name="city_id" class="panel-select">
                <option value="">All cities</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>{{ $city->name }}</option>
                @endforeach
            </select>
            <select name="status" class="panel-select">
                <option value="">Booking status</option>
                @foreach (['pending', 'confirmed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <select name="payment_status" class="panel-select">
                <option value="">Payment status</option>
                @foreach (['pending', 'paid', 'failed', 'refunded'] as $status)
                    <option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ ucfirst($status) }}</option>
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
                        <th class="px-6 py-4">Reference</th>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Event</th>
                        <th class="px-6 py-4">Seats</th>
                        <th class="px-6 py-4">Booking</th>
                        <th class="px-6 py-4">Payment</th>
                        <th class="px-6 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($bookings as $booking)
                        <tr>
                            <td class="px-6 py-4 font-semibold">{{ $booking->booking_reference }}</td>
                            <td class="px-6 py-4">{{ $booking->user->name }}</td>
                            <td class="px-6 py-4">
                                <p>{{ $booking->event->title }}</p>
                                <p class="text-sm text-slate-500">{{ $booking->show->venue->cityRecord?->name ?? $booking->show->venue->city }}</p>
                            </td>
                            <td class="px-6 py-4">{{ $booking->seats }}</td>
                            <td class="px-6 py-4"><span class="panel-badge bg-slate-100 text-slate-700">{{ $booking->status }}</span></td>
                            <td class="px-6 py-4"><span class="panel-badge bg-emerald-100 text-emerald-700">{{ $booking->payment_status }}</span></td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="panel-btn-secondary">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $bookings->links() }}</div>
    </div>
</x-app-layout>
