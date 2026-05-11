<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Venue Details</p>
            <h2 class="text-3xl font-semibold text-slate-900">{{ $venue->name }}</h2>
        </div>
        <a href="{{ route('admin.venues.edit', $venue) }}" class="panel-btn-secondary">Edit Venue</a>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="panel-card p-6">
            <div class="space-y-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">City</p>
                    <p class="mt-1 font-semibold">{{ $venue->cityRecord?->name ?? $venue->city }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Address</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $venue->address }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Capacity</p>
                    <p class="mt-1 font-semibold">{{ $venue->total_seats }} seats | {{ $venue->row_count }} x {{ $venue->column_count }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Screen / Stage Label</p>
                    <p class="mt-1 font-semibold">
                        {{ $venue->layout_label_position === 'hidden' ? 'Hidden' : (($venue->layout_label ?? 'SCREEN').' - '.ucfirst($venue->layout_label_position ?? 'bottom')) }}
                    </p>
                </div>
                @if ($venue->map_url)
                    <a href="{{ $venue->map_url }}" target="_blank" class="panel-btn-secondary w-full">Open Map</a>
                @endif
                @if ($venue->layout_image)
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Layout Reference</p>
                        <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                            <img src="{{ asset('storage/'.$venue->layout_image) }}" alt="Venue layout reference" class="max-h-72 w-full object-contain">
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="panel-card p-6 xl:col-span-2">
            <h3 class="text-lg font-semibold">Scheduled Shows</h3>
            <div class="mt-4 space-y-3">
                @forelse ($venue->shows as $show)
                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold">{{ $show->event->title }}</p>
                                <p class="text-sm text-slate-500">{{ $show->show_date->format('d M Y') }} at {{ \Carbon\Carbon::parse($show->show_time)->format('h:i A') }}</p>
                            </div>
                            <a href="{{ route('admin.shows.show', $show) }}" class="panel-btn-secondary">Open</a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No shows assigned to this venue yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
