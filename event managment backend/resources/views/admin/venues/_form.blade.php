@csrf

<div class="panel-card p-6">
    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <label class="panel-label" for="name">Name</label>
            <input id="name" name="name" type="text" class="panel-input" value="{{ old('name', $venue->name ?? '') }}" required>
        </div>
        <div>
            <label class="panel-label" for="slug">Slug</label>
            <input id="slug" name="slug" type="text" class="panel-input" value="{{ old('slug', $venue->slug ?? '') }}">
        </div>
        <div>
            <label class="panel-label" for="city_id">City</label>
            <select id="city_id" name="city_id" class="panel-select">
                <option value="">Select city</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" @selected(old('city_id', $venue->city_id ?? '') == $city->id)>{{ $city->name }} - {{ $city->countryRecord?->name ?? $city->country }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="panel-label" for="address">Address</label>
            <textarea id="address" name="address" rows="4" class="panel-input" required>{{ old('address', $venue->address ?? '') }}</textarea>
        </div>
        <div>
            <label class="panel-label" for="total_seats">Total Seats</label>
            <input id="total_seats" name="total_seats" type="number" min="1" class="panel-input" value="{{ old('total_seats', $venue->total_seats ?? '') }}" required>
        </div>
        <div>
            <label class="panel-label" for="row_count">Rows</label>
            <input id="row_count" name="row_count" type="number" min="1" class="panel-input" value="{{ old('row_count', $venue->row_count ?? 10) }}" required>
        </div>
        <div>
            <label class="panel-label" for="column_count">Columns</label>
            <input id="column_count" name="column_count" type="number" min="1" class="panel-input" value="{{ old('column_count', $venue->column_count ?? 12) }}" required>
        </div>
        <div>
            <label class="panel-label" for="latitude">Latitude</label>
            <input id="latitude" name="latitude" type="text" class="panel-input" value="{{ old('latitude', $venue->latitude ?? '') }}">
        </div>
        <div>
            <label class="panel-label" for="longitude">Longitude</label>
            <input id="longitude" name="longitude" type="text" class="panel-input" value="{{ old('longitude', $venue->longitude ?? '') }}">
        </div>
        <div class="md:col-span-2">
            <label class="panel-label" for="map_url">Map URL</label>
            <input id="map_url" name="map_url" type="url" class="panel-input" value="{{ old('map_url', $venue->map_url ?? '') }}">
        </div>
        <div>
            <label class="panel-label" for="layout_label">Screen / Stage Label</label>
            <input id="layout_label" name="layout_label" type="text" class="panel-input" value="{{ old('layout_label', $venue->layout_label ?? 'SCREEN') }}" placeholder="SCREEN, STAGE, ENTRY">
            <p class="mt-1 text-xs text-slate-500">Change this for venues without a screen, like STAGE or ENTRY.</p>
        </div>
        <div>
            <label class="panel-label" for="layout_label_position">Label Position</label>
            <select id="layout_label_position" name="layout_label_position" class="panel-select">
                @foreach (['bottom' => 'Bottom', 'top' => 'Top', 'hidden' => 'Hidden / no screen'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('layout_label_position', $venue->layout_label_position ?? 'bottom') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Use hidden when the event has no screen or stage direction.</p>
        </div>
        <div class="md:col-span-2">
            <label class="panel-label" for="layout_image">Venue Layout Image</label>
            <input id="layout_image" name="layout_image" type="file" accept="image/*" class="panel-input">
            <p class="mt-1 text-xs text-slate-500">Optional reference image. Seats are still generated from rows and columns, then blocked manually for aisles/gaps.</p>
            @if (! empty($venue?->layout_image))
                <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                    <img src="{{ asset('storage/'.$venue->layout_image) }}" alt="Venue layout reference" class="max-h-72 w-full object-contain bg-slate-50">
                </div>
                <label class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remove_layout_image" value="1">
                    Remove current layout image
                </label>
            @endif
        </div>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit" class="panel-btn">{{ $buttonText }}</button>
    <a href="{{ route('admin.venues.index') }}" class="panel-btn-secondary">Cancel</a>
</div>
