<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Event Details</p>
            <h2 class="text-3xl font-semibold text-slate-900">{{ $event->title }}</h2>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.events.shows.create', $event) }}" class="panel-btn">Add Another Date</a>
            <a href="{{ route('admin.events.edit', $event) }}" class="panel-btn-secondary">Edit Event</a>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6">
            <div class="panel-card overflow-hidden">
                @if ($event->banner_image)
                    <img src="{{ asset('storage/'.$event->banner_image) }}" alt="{{ $event->title }}" class="h-48 w-full object-cover">
                @elseif ($event->poster_image)
                    <img src="{{ asset('storage/'.$event->poster_image) }}" alt="{{ $event->title }}" class="h-48 w-full object-cover">
                @endif
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Category</p>
                            <p class="mt-1 font-semibold">{{ $event->eventCategory?->name ?? $event->category }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Tags</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($event->tags as $tag)
                                    <span class="panel-badge bg-slate-100 text-slate-700">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Publishing</p>
                            <p class="mt-1 font-semibold">{{ ucfirst($event->publication_status) }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">SEO</p>
                            <p class="mt-1 font-semibold">{{ $event->meta_title ?: 'Not configured' }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $event->meta_description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Gallery</h3>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @forelse ($event->galleryImages as $image)
                        <img src="{{ asset('storage/'.$image->image_path) }}" alt="" class="h-32 w-full rounded-2xl object-cover">
                    @empty
                        <p class="text-sm text-slate-500">No gallery images added.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6 xl:col-span-2">
            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Description</h3>
                <p class="mt-4 leading-7 text-slate-600">{{ $event->description }}</p>
            </div>

            <div class="panel-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Main Ticket Listing</h3>
                        <p class="mt-1 text-sm text-slate-500">This is the direct listing customers will book from first.</p>
                    </div>
                    @if ($event->primaryShow)
                        <a href="{{ route('admin.shows.show', $event->primaryShow) }}" class="panel-btn-secondary">Seat Map</a>
                    @endif
                </div>

                @if ($event->primaryShow)
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Venue</p>
                            <p class="mt-2 font-semibold">{{ $event->primaryShow->venue->name }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $event->primaryShow->venue->address }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Market</p>
                            <p class="mt-2 font-semibold">{{ $event->primaryShow->venue->cityRecord?->name ?? $event->primaryShow->venue->city }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $event->primaryShow->venue->cityRecord?->state }}, {{ $event->primaryShow->venue->cityRecord?->country }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Date & Time</p>
                            <p class="mt-2 font-semibold">{{ $event->primaryShow->show_date->format('d M Y') }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ \Carbon\Carbon::parse($event->primaryShow->show_time)->format('h:i A') }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Base Ticket Price</p>
                            <p class="mt-2 font-semibold">{{ \App\Support\Currency::inr($event->primaryShow->price) }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Available Seats</p>
                            <p class="mt-2 font-semibold">{{ $event->primaryShow->available_seats }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Listing Status</p>
                            <p class="mt-2 font-semibold">{{ ucfirst($event->primaryShow->status) }}</p>
                            <p class="mt-1 text-sm text-slate-500">Seat lock: {{ $event->primaryShow->seat_lock_minutes }} min</p>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-500">No ticket listing configured yet.</p>
                @endif
            </div>

            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Campaign Timeline</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($event->timelines as $timeline)
                        <div class="rounded-2xl border border-slate-100 px-4 py-3">
                            <p class="font-semibold">{{ $timeline->title }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $timeline->description }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ optional($timeline->starts_at)->format('d M Y h:i A') }} - {{ optional($timeline->ends_at)->format('d M Y h:i A') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No timeline added.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">All Ticket Dates</h3>
                        <p class="mt-1 text-sm text-slate-500">Use this only when the same event runs on multiple dates or venues.</p>
                    </div>
                    <span class="text-sm text-slate-500">{{ $event->shows->count() }} scheduled</span>
                </div>
                <div class="space-y-3">
                    @foreach ($event->shows as $show)
                        <div class="rounded-2xl border border-slate-100 px-4 py-3">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold">{{ $show->show_date->format('d M Y') }} {{ \Carbon\Carbon::parse($show->show_time)->format('h:i A') }}</p>
                                        @if ($event->primaryShow && $show->is($event->primaryShow))
                                            <span class="panel-badge bg-emerald-100 text-emerald-700">Main Listing</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-slate-500">{{ $show->venue->name }} | {{ $show->venue->cityRecord?->name ?? $show->venue->city }} | {{ \App\Support\Currency::inr($show->price, 0) }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.shows.show', $show) }}" class="panel-btn-secondary">Seat Map</a>
                                    <a href="{{ route('admin.shows.edit', $show) }}" class="panel-btn-secondary">Edit</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
