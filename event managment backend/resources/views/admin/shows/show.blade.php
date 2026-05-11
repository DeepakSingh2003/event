<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Show Seat Map</p>
            <h2 class="text-3xl font-semibold text-slate-900">{{ $show->event->title }}</h2>
        </div>
        <div class="flex gap-3">
            <form action="{{ route('admin.shows.regenerate-seats', $show) }}" method="POST">
                @csrf
                <button class="panel-btn-secondary" type="submit">Regenerate Layout</button>
            </form>
            <a href="{{ route('admin.shows.edit', $show) }}" class="panel-btn">Edit Show</a>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Seat Layout</h3>
                @if ($show->booking_mode !== 'reserved_seating')
                    <p class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">This show uses {{ str_replace('_', ' ', $show->booking_mode) }} and does not require a seat map.</p>
                @else
                @if ($show->venue->layout_image)
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                        <div class="border-b border-slate-200 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">Venue reference image</p>
                            <p class="text-xs text-slate-500">Use this image to decide which generated seats should be blocked for aisles, gaps, reserved areas, or unavailable sections.</p>
                        </div>
                        <img src="{{ asset('storage/'.$show->venue->layout_image) }}" alt="Venue layout reference" class="max-h-[420px] w-full object-contain">
                    </div>
                @endif
                <form action="{{ route('admin.shows.seat-status', $show) }}" method="POST" class="mt-4 rounded-2xl border border-slate-200 p-4">
                    @csrf
                    @method('PATCH')
                    <div class="flex flex-wrap items-end gap-3">
                        <div class="flex-1">
                            <label class="panel-label">Seat IDs</label>
                            <input name="seat_ids[]" class="panel-input" placeholder="Use checkboxes below or type one id">
                        </div>
                        <select name="status" class="panel-select w-44">
                            <option value="blocked">Block / reserve</option>
                            <option value="available">Make available</option>
                        </select>
                        <button class="panel-btn" type="submit">Update Seats</button>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Booked or locked seats are protected and will not be changed.</p>
                </form>
                <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                    @php
                        $layoutLabel = $show->venue->layout_label ?: 'SCREEN';
                        $layoutLabelPosition = $show->venue->layout_label_position ?: 'bottom';
                    @endphp
                    @if ($layoutLabelPosition === 'top')
                        <div class="mb-6 rounded-2xl bg-slate-900 px-6 py-3 text-center text-sm font-semibold text-white">{{ $layoutLabel }}</div>
                    @endif
                    <div class="space-y-3 overflow-x-auto">
                        @foreach ($show->seats->groupBy('row_label') as $row => $rowSeats)
                            <div class="flex items-center gap-3">
                                <span class="w-8 text-sm font-semibold text-slate-500">{{ $row }}</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($rowSeats as $seat)
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl text-xs font-semibold text-white
                                            {{ $seat->status === 'available' ? 'bg-emerald-500' : ($seat->status === 'locked' ? 'bg-amber-500' : ($seat->status === 'booked' ? 'bg-rose-500' : 'bg-slate-500')) }}">
                                            <label class="flex h-full w-full cursor-pointer items-center justify-center">
                                                <input type="checkbox" form="bulk-seat-status" name="seat_ids[]" value="{{ $seat->id }}" class="hidden">
                                                {{ $seat->column_number }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if ($layoutLabelPosition === 'bottom')
                        <div class="mt-6 rounded-2xl bg-slate-900 px-6 py-3 text-center text-sm font-semibold text-white">{{ $layoutLabel }}</div>
                    @endif
                </div>
                <div class="mt-4 flex flex-wrap gap-3 text-sm">
                    <span class="panel-badge bg-emerald-100 text-emerald-700">Available</span>
                    <span class="panel-badge bg-amber-100 text-amber-700">Locked</span>
                    <span class="panel-badge bg-rose-100 text-rose-700">Booked</span>
                    <span class="panel-badge bg-slate-100 text-slate-700">Blocked / reserved by admin</span>
                </div>
                <form id="bulk-seat-status" action="{{ route('admin.shows.seat-status', $show) }}" method="POST" class="mt-4 flex flex-wrap gap-3">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="panel-select w-44">
                        <option value="blocked">Block selected</option>
                        <option value="available">Unblock selected</option>
                    </select>
                    <button class="panel-btn-secondary" type="submit">Apply to selected</button>
                </form>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Show Summary</h3>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <p><span class="font-semibold text-slate-900">Venue:</span> {{ $show->venue->name }}</p>
                    <p><span class="font-semibold text-slate-900">City:</span> {{ $show->venue->cityRecord?->name ?? $show->venue->city }}</p>
                    <p><span class="font-semibold text-slate-900">Date:</span> {{ $show->show_date->format('d M Y') }}</p>
                    <p><span class="font-semibold text-slate-900">Time:</span> {{ \Carbon\Carbon::parse($show->show_time)->format('h:i A') }}</p>
                    <p><span class="font-semibold text-slate-900">Status:</span> {{ ucfirst($show->status) }}</p>
                </div>
            </div>

            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Dynamic Pricing</h3>
                <form action="{{ route('admin.shows.seat-pricing', $show) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')
                    @foreach ($seatTypes as $seatType)
                        <div>
                            <label class="panel-label">{{ $seatType->name }}</label>
                            <input type="number" step="0.01" name="seat_prices[{{ $seatType->id }}]" value="{{ optional($show->seats->firstWhere('seat_type_id', $seatType->id))->price }}" class="panel-input">
                        </div>
                    @endforeach
                    <button class="panel-btn" type="submit">Update Pricing</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
