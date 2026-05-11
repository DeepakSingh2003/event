<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Seat Types</p>
            <h2 class="text-3xl font-semibold text-slate-900">Seat Name, Color & Multiplier</h2>
        </div>
    </x-slot>

    <div x-data="{ open: false }" class="space-y-6">
        <div class="flex items-center justify-between gap-4 panel-card p-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Global seat categories</h3>
                <p class="mt-1 text-sm text-slate-500">These names, colors, and multipliers are used when a show seat map is generated. Per-show price override is still available on each show page.</p>
            </div>
            <button class="panel-btn" @click="open = ! open" x-text="open ? 'Close Form' : 'Add Seat Type'"></button>
        </div>

        <div x-show="open" x-transition class="panel-card p-6">
            <form action="{{ route('admin.seat-types.store') }}" method="POST" class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                @csrf
                <input type="text" name="name" class="panel-input" placeholder="Name, e.g. Platinum" value="{{ old('name') }}" required>
                <input type="text" name="code" class="panel-input" placeholder="Code, e.g. PLATINUM" value="{{ old('code') }}">
                <input type="color" name="color" class="h-12 rounded-2xl border border-slate-200 bg-white px-2" value="{{ old('color', '#2563eb') }}" required>
                <input type="number" step="0.01" min="0" name="price_multiplier" class="panel-input" placeholder="Multiplier" value="{{ old('price_multiplier', 1) }}" required>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-rose-500">
                    Active
                </label>
                <button class="panel-btn" type="submit">Save</button>
                <input type="text" name="description" class="panel-input md:col-span-2 xl:col-span-6" placeholder="Description" value="{{ old('description') }}">
            </form>
        </div>
    </div>

    <div class="panel-card mt-6 overflow-hidden">
        <table class="panel-table">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Code</th>
                    <th class="px-6 py-4">Color</th>
                    <th class="px-6 py-4">Multiplier</th>
                    <th class="px-6 py-4">Seats</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($seatTypes as $seatType)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $seatType->name }}</td>
                        <td class="px-6 py-4"><code class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $seatType->code }}</code></td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-2 text-sm text-slate-600">
                                <span class="h-5 w-5 rounded-full border border-slate-200" style="background-color: {{ $seatType->color }}"></span>
                                {{ $seatType->color }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ number_format((float) $seatType->price_multiplier, 2) }}x</td>
                        <td class="px-6 py-4">{{ $seatType->show_seats_count }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $seatType->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $seatType->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <details>
                                <summary class="cursor-pointer text-sm font-semibold text-slate-700">Edit</summary>
                                <div class="mt-4 rounded-3xl border border-slate-100 bg-slate-50 p-4">
                                    <form action="{{ route('admin.seat-types.update', $seatType) }}" method="POST" class="grid gap-3 lg:grid-cols-6">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" class="panel-input" value="{{ $seatType->name }}" required>
                                        <input type="text" name="code" class="panel-input" value="{{ $seatType->code }}" @readonly(in_array($seatType->code, ['VIP', 'GOLD', 'SILVER', 'NORMAL'], true) || $seatType->show_seats_count)>
                                        <input type="color" name="color" class="h-12 rounded-2xl border border-slate-200 bg-white px-2" value="{{ $seatType->color }}" required>
                                        <input type="number" step="0.01" min="0" name="price_multiplier" class="panel-input" value="{{ $seatType->price_multiplier }}" required>
                                        <label class="flex items-center gap-2 text-sm text-slate-600">
                                            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-rose-500" @checked($seatType->is_active)>
                                            Active
                                        </label>
                                        <label class="flex items-center gap-2 text-sm text-slate-600">
                                            <input type="checkbox" name="apply_existing" value="1" class="rounded border-slate-300 text-rose-500">
                                            Apply to existing unbooked seats
                                        </label>
                                        <input type="text" name="description" class="panel-input lg:col-span-6" value="{{ $seatType->description }}" placeholder="Description">
                                        <div class="lg:col-span-6 flex flex-wrap justify-end gap-2">
                                            <button class="panel-btn" type="submit">Update</button>
                                        </div>
                                    </form>
                                    <form action="{{ route('admin.seat-types.destroy', $seatType) }}" method="POST" class="mt-3 flex justify-end">
                                        @csrf
                                        @method('DELETE')
                                        <button class="panel-btn-secondary" type="submit">{{ $seatType->show_seats_count ? 'Mark Inactive' : 'Delete' }}</button>
                                    </form>
                                </div>
                            </details>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-6 py-4">{{ $seatTypes->links() }}</div>
    </div>
</x-app-layout>
