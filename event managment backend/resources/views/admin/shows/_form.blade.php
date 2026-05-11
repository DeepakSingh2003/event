@csrf

<div class="panel-card p-6">
    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <label class="panel-label" for="venue_id">Venue</label>
            <select id="venue_id" name="venue_id" class="panel-select" required>
                <option value="">Select venue</option>
                @foreach ($venues as $venue)
                    <option value="{{ $venue->id }}" @selected(old('venue_id', $show->venue_id ?? '') == $venue->id)>{{ $venue->name }} - {{ $venue->cityRecord?->name ?? $venue->city }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="panel-label" for="status">Status</label>
            <select id="status" name="status" class="panel-select" required>
                @foreach (['scheduled', 'cancelled', 'sold_out'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $show->status ?? 'scheduled') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="panel-label" for="booking_mode">Booking Mode</label>
            <select id="booking_mode" name="booking_mode" class="panel-select" required>
                @foreach (['reserved_seating' => 'Reserved seating seat map', 'general_admission' => 'Direct ticket quantity', 'tiered_tickets' => 'Ticket types without seats'] as $mode => $label)
                    <option value="{{ $mode }}" @selected(old('booking_mode', $show->booking_mode ?? 'reserved_seating') === $mode)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="panel-label" for="show_date">Date</label>
            <input id="show_date" name="show_date" type="date" class="panel-input" value="{{ old('show_date', isset($show) ? $show->show_date->format('Y-m-d') : '') }}" required>
        </div>
        <div>
            <label class="panel-label" for="show_time">Time</label>
            <input id="show_time" name="show_time" type="time" class="panel-input" value="{{ old('show_time', isset($show) ? \Carbon\Carbon::parse($show->show_time)->format('H:i') : '') }}" required>
        </div>
        <div>
            <label class="panel-label" for="price">Base Price</label>
            <input id="price" name="price" type="number" step="0.01" min="0" class="panel-input" value="{{ old('price', $show->price ?? '') }}" required>
        </div>
        <div>
            <label class="panel-label" for="currency_code">Currency</label>
            <select id="currency_code" name="currency_code" class="panel-select" required>
                @foreach (['INR', 'USD', 'EUR', 'GBP', 'SGD', 'AED', 'BRL'] as $currencyCode)
                    <option value="{{ $currencyCode }}" @selected(old('currency_code', $show->currency_code ?? 'INR') === $currencyCode)>{{ $currencyCode }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="panel-label" for="available_seats">Available Seats</label>
            <input id="available_seats" name="available_seats" type="number" min="1" class="panel-input" value="{{ old('available_seats', $show->available_seats ?? '') }}" required>
        </div>
        <div>
            <label class="panel-label" for="sales_capacity">Sales Capacity</label>
            <input id="sales_capacity" name="sales_capacity" type="number" min="1" class="panel-input" value="{{ old('sales_capacity', $show->sales_capacity ?? '') }}">
            <p class="mt-1 text-xs text-slate-500">Used for direct ticket shows. Leave blank to use available seats.</p>
        </div>
        <div>
            <label class="panel-label" for="booking_open_at">Booking Open At</label>
            <input id="booking_open_at" name="booking_open_at" type="datetime-local" class="panel-input" value="{{ old('booking_open_at', isset($show) && $show->booking_open_at ? $show->booking_open_at->format('Y-m-d\TH:i') : '') }}">
        </div>
        <div>
            <label class="panel-label" for="booking_close_at">Booking Close At</label>
            <input id="booking_close_at" name="booking_close_at" type="datetime-local" class="panel-input" value="{{ old('booking_close_at', isset($show) && $show->booking_close_at ? $show->booking_close_at->format('Y-m-d\TH:i') : '') }}">
        </div>
        <div>
            <label class="panel-label" for="seat_lock_minutes">Seat Lock Minutes</label>
            <input id="seat_lock_minutes" name="seat_lock_minutes" type="number" min="1" max="30" class="panel-input" value="{{ old('seat_lock_minutes', $show->seat_lock_minutes ?? 10) }}" required>
        </div>
    </div>
</div>

<div class="panel-card mt-6 p-6">
    <h3 class="text-lg font-semibold text-slate-900">Ticket Types</h3>
    <p class="mt-1 text-sm text-slate-500">Used only when booking mode is "Ticket types without seats". Example: VIP, Normal, Early Bird.</p>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @php
            $oldTicketTypes = old('ticket_types', isset($show) ? $show->ticketTypes->map(fn ($ticketType) => [
                'name' => $ticketType->name,
                'code' => $ticketType->code,
                'price' => $ticketType->price,
                'capacity' => $ticketType->capacity,
                'description' => $ticketType->description,
            ])->toArray() : []);
            $ticketRows = array_pad($oldTicketTypes, 3, []);
        @endphp
        @foreach ($ticketRows as $index => $ticketType)
            <div class="rounded-2xl border border-slate-200 p-4">
                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="panel-label">Name</label>
                        <input name="ticket_types[{{ $index }}][name]" class="panel-input" value="{{ $ticketType['name'] ?? '' }}" placeholder="VIP">
                    </div>
                    <div>
                        <label class="panel-label">Code</label>
                        <input name="ticket_types[{{ $index }}][code]" class="panel-input" value="{{ $ticketType['code'] ?? '' }}" placeholder="VIP">
                    </div>
                    <div>
                        <label class="panel-label">Price</label>
                        <input name="ticket_types[{{ $index }}][price]" type="number" step="0.01" min="0" class="panel-input" value="{{ $ticketType['price'] ?? '' }}">
                    </div>
                    <div>
                        <label class="panel-label">Capacity</label>
                        <input name="ticket_types[{{ $index }}][capacity]" type="number" min="1" class="panel-input" value="{{ $ticketType['capacity'] ?? '' }}">
                    </div>
                    <div class="md:col-span-2">
                        <label class="panel-label">Description</label>
                        <input name="ticket_types[{{ $index }}][description]" class="panel-input" value="{{ $ticketType['description'] ?? '' }}" placeholder="Best view, front section">
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit" class="panel-btn">{{ $buttonText }}</button>
    <a href="{{ route('admin.events.show', $event ?? $show->event) }}" class="panel-btn-secondary">Cancel</a>
</div>
