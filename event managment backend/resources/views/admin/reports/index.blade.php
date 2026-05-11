<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <!-- Left Side -->
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">
                Reports
            </p>
            <h2 class="text-3xl font-semibold text-slate-900">
                Analytics & Reports
            </h2>
        </div>

        <!-- Right Side Buttons -->
        <div class="flex gap-3">
            <a href="{{ route('admin.reports.export.csv', request()->query()) }}" class="panel-btn-secondary">
                Export CSV
            </a>
            <a href="{{ route('admin.reports.export.pdf', request()->query()) }}" class="panel-btn">
                Export PDF
            </a>
        </div>

    </div>
</x-slot>

    <div class="panel-card p-6">
        <form method="GET" class="grid gap-4 md:grid-cols-5">
            <input type="date" name="from" value="{{ $report['filters']['from'] }}" class="panel-input">
            <input type="date" name="to" value="{{ $report['filters']['to'] }}" class="panel-input">

            <select name="city_id" class="panel-select">
                <option value="">All cities</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" @selected((string) $report['filters']['city_id'] === (string) $city->id)>
                        {{ $city->name }}
                    </option>
                @endforeach
            </select>

            <select name="event_id" class="panel-select">
                <option value="">All events</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" @selected((string) $report['filters']['event_id'] === (string) $event->id)>
                        {{ $event->title }}
                    </option>
                @endforeach
            </select>

            <button class="panel-btn" type="submit">Apply</button>
        </form>
    </div>

    <!-- Stats -->
    <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="panel-stat">
            <p class="text-sm text-slate-500">Revenue</p>
            <p class="mt-3 text-4xl font-semibold">
                {{ \App\Support\Currency::inr($report['revenue']) }}
            </p>
        </div>

        <div class="panel-stat">
            <p class="text-sm text-slate-500">Bookings</p>
            <p class="mt-3 text-4xl font-semibold">
                {{ $report['bookings'] }}
            </p>
        </div>

        <div class="panel-stat">
            <p class="text-sm text-slate-500">Cancelled</p>
            <p class="mt-3 text-4xl font-semibold">
                {{ $report['cancelled'] }}
            </p>
        </div>

        <div class="panel-stat">
            <p class="text-sm text-slate-500">Failed Payments</p>
            <p class="mt-3 text-4xl font-semibold">
                {{ $report['failed_payments'] }}
            </p>
        </div>
    </div>

    <!-- Top Events & Users -->
    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="panel-card p-6">
            <h3 class="text-lg font-semibold">Top Events</h3>
            <div class="mt-4 space-y-3">
                @foreach ($report['topEvents'] as $event)
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="font-semibold">{{ $event->title }}</p>
                        <span class="text-sm text-slate-500">
                            {{ $event->bookings_count }} bookings
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="panel-card p-6">
            <h3 class="text-lg font-semibold">Most Active Users</h3>
            <div class="mt-4 space-y-3">
                @foreach ($report['activeUsers'] as $user)
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="font-semibold">{{ $user->name }}</p>
                        <span class="text-sm text-slate-500">
                            {{ $user->bookings_count }} bookings
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>