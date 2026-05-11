@php
    $initialTimeline = old('timeline', isset($event)
        ? $event->timelines->map(fn ($timeline) => [
            'title' => $timeline->title,
            'description' => $timeline->description,
            'starts_at' => optional($timeline->starts_at)->format('Y-m-d\TH:i'),
            'ends_at' => optional($timeline->ends_at)->format('Y-m-d\TH:i'),
        ])->values()->all()
        : [['title' => '', 'description' => '', 'starts_at' => '', 'ends_at' => '']]);

    $primaryShow = $event->primaryShow ?? null;
    $selectedTags = old('tag_ids', isset($event) ? $event->tags->pluck('id')->all() : []);
@endphp

@csrf

<div class="grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <div class="panel-card p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold">Event Details</h3>
                    <p class="mt-1 text-sm text-slate-500">Create the public event details your customers will see.</p>
                </div>
                <span class="panel-chip border border-emerald-100 bg-emerald-50 text-emerald-700">Direct ticket listing flow</span>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="panel-label" for="title">Event Title</label>
                    <input id="title" name="title" type="text" class="panel-input" value="{{ old('title', $event->title ?? '') }}" required>
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <label class="panel-label" for="slug">Slug</label>
                    <input id="slug" name="slug" type="text" class="panel-input" value="{{ old('slug', $event->slug ?? '') }}">
                </div>

                <div>
                    <label class="panel-label" for="language">Language</label>
                    <input id="language" name="language" type="text" class="panel-input" value="{{ old('language', $event->language ?? '') }}" required>
                </div>

                <div>
                    <label class="panel-label" for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="panel-select">
                        <option value="">Select category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $event->category_id ?? '') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="panel-label" for="tag_ids">Tags</label>
                    <select id="tag_ids" name="tag_ids[]" multiple class="panel-select h-36">
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}" @selected(in_array($tag->id, $selectedTags))>{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="panel-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="5" class="panel-input" required>{{ old('description', $event->description ?? '') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="panel-card p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold">Ticket Listing</h3>
                    <p class="mt-1 text-sm text-slate-500">Set venue, date, time, and base ticket price directly while creating the event.</p>
                </div>
                <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">1 event = 1 primary listing</span>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="panel-label" for="listing_venue_id">Venue</label>
                    <select id="listing_venue_id" name="listing_venue_id" class="panel-select" required>
                        <option value="">Select venue</option>
                        @foreach ($venues as $venue)
                            <option value="{{ $venue->id }}" @selected(old('listing_venue_id', $primaryShow?->venue_id) == $venue->id)>
                                {{ $venue->name }} - {{ $venue->cityRecord?->name ?? $venue->city }}, {{ $venue->cityRecord?->country }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('listing_venue_id')" class="mt-2" />
                    <p class="mt-2 text-xs text-slate-500">Seat capacity is automatically taken from the selected venue layout.</p>
                </div>

                <div>
                    <label class="panel-label" for="listing_show_date">Event Date</label>
                    <input id="listing_show_date" name="listing_show_date" type="date" class="panel-input" value="{{ old('listing_show_date', $primaryShow?->show_date?->format('Y-m-d')) }}" required>
                    <x-input-error :messages="$errors->get('listing_show_date')" class="mt-2" />
                </div>

                <div>
                    <label class="panel-label" for="listing_show_time">Event Time</label>
                    <input id="listing_show_time" name="listing_show_time" type="time" class="panel-input" value="{{ old('listing_show_time', $primaryShow && $primaryShow->show_time ? \Illuminate\Support\Carbon::parse($primaryShow->show_time)->format('H:i') : '') }}" required>
                    <x-input-error :messages="$errors->get('listing_show_time')" class="mt-2" />
                </div>

                <div>
                    <label class="panel-label" for="listing_price">Base Ticket Price</label>
                    <input id="listing_price" name="listing_price" type="number" step="0.01" min="0" class="panel-input" value="{{ old('listing_price', $primaryShow?->price) }}" required>
                    <x-input-error :messages="$errors->get('listing_price')" class="mt-2" />
                </div>

                <div>
                    <label class="panel-label" for="listing_currency_code">Currency</label>
                    <select id="listing_currency_code" name="listing_currency_code" class="panel-select" required>
                        @foreach (['INR', 'USD', 'EUR', 'GBP', 'SGD', 'AED', 'BRL'] as $currencyCode)
                            <option value="{{ $currencyCode }}" @selected(old('listing_currency_code', $primaryShow?->currency_code ?? 'INR') === $currencyCode)>{{ $currencyCode }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('listing_currency_code')" class="mt-2" />
                </div>

                <div>
                    <label class="panel-label" for="listing_status">Listing Status</label>
                    <select id="listing_status" name="listing_status" class="panel-select" required>
                        @foreach (['scheduled', 'cancelled', 'sold_out'] as $status)
                            <option value="{{ $status }}" @selected(old('listing_status', $primaryShow?->status ?? 'scheduled') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('listing_status')" class="mt-2" />
                </div>

                <div>
                    <label class="panel-label" for="listing_booking_open_at">Booking Opens</label>
                    <input id="listing_booking_open_at" name="listing_booking_open_at" type="datetime-local" class="panel-input" value="{{ old('listing_booking_open_at', $primaryShow?->booking_open_at?->format('Y-m-d\TH:i')) }}">
                    <x-input-error :messages="$errors->get('listing_booking_open_at')" class="mt-2" />
                </div>

                <div>
                    <label class="panel-label" for="listing_booking_close_at">Booking Closes</label>
                    <input id="listing_booking_close_at" name="listing_booking_close_at" type="datetime-local" class="panel-input" value="{{ old('listing_booking_close_at', $primaryShow?->booking_close_at?->format('Y-m-d\TH:i')) }}">
                    <x-input-error :messages="$errors->get('listing_booking_close_at')" class="mt-2" />
                </div>

                <div>
                    <label class="panel-label" for="listing_seat_lock_minutes">Seat Lock Minutes</label>
                    <input id="listing_seat_lock_minutes" name="listing_seat_lock_minutes" type="number" min="1" max="30" class="panel-input" value="{{ old('listing_seat_lock_minutes', $primaryShow?->seat_lock_minutes ?? 10) }}" required>
                    <x-input-error :messages="$errors->get('listing_seat_lock_minutes')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="panel-card p-6">
            <h3 class="text-lg font-semibold">SEO & Publishing</h3>
            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div>
                    <label class="panel-label" for="publication_status">Publication Status</label>
                    <select id="publication_status" name="publication_status" class="panel-select" required>
                        @foreach (['draft', 'published', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(old('publication_status', $event->publication_status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="panel-label" for="published_at">Publish At</label>
                    <input id="published_at" name="published_at" type="datetime-local" class="panel-input" value="{{ old('published_at', isset($event) && $event->published_at ? $event->published_at->format('Y-m-d\TH:i') : '') }}">
                </div>

                <div class="md:col-span-2">
                    <label class="panel-label" for="meta_title">Meta Title</label>
                    <input id="meta_title" name="meta_title" type="text" class="panel-input" value="{{ old('meta_title', $event->meta_title ?? '') }}">
                </div>

                <div class="md:col-span-2">
                    <label class="panel-label" for="meta_description">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" rows="3" class="panel-input">{{ old('meta_description', $event->meta_description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="panel-card p-6" x-data='{
            timeline: @json($initialTimeline),
            addRow() { this.timeline.push({title: "", description: "", starts_at: "", ends_at: ""}) },
            removeRow(index) { this.timeline.splice(index, 1) }
        }'>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">Campaign Timeline</h3>
                    <p class="mt-1 text-sm text-slate-500">Optional milestones like teaser launch, booking start, and event day highlights.</p>
                </div>
                <button type="button" class="panel-btn-secondary" @click="addRow()">Add Timeline Item</button>
            </div>
            <div class="mt-6 space-y-4">
                <template x-for="(item, index) in timeline" :key="index">
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="panel-label">Title</label>
                                <input :name="`timeline[${index}][title]`" x-model="item.title" type="text" class="panel-input">
                            </div>
                            <div class="md:col-span-2">
                                <label class="panel-label">Description</label>
                                <textarea :name="`timeline[${index}][description]`" x-model="item.description" rows="2" class="panel-input"></textarea>
                            </div>
                            <div>
                                <label class="panel-label">Starts At</label>
                                <input :name="`timeline[${index}][starts_at]`" x-model="item.starts_at" type="datetime-local" class="panel-input">
                            </div>
                            <div>
                                <label class="panel-label">Ends At</label>
                                <input :name="`timeline[${index}][ends_at]`" x-model="item.ends_at" type="datetime-local" class="panel-input">
                            </div>
                        </div>
                        <button type="button" class="mt-4 text-sm font-semibold text-rose-500" @click="removeRow(index)" x-show="timeline.length > 1">Remove</button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="panel-card p-6">
            <h3 class="text-lg font-semibold">Images & Visibility</h3>
            <div class="mt-6 space-y-5">
                <div>
                    <label class="panel-label" for="poster_image">Poster Image</label>
                    <input id="poster_image" name="poster_image" type="file" class="panel-input">
                </div>
                <div>
                    <label class="panel-label" for="banner_image">Banner Image</label>
                    <input id="banner_image" name="banner_image" type="file" class="panel-input">
                </div>
                <div>
                    <label class="panel-label" for="gallery_images">Gallery Images</label>
                    <input id="gallery_images" name="gallery_images[]" type="file" class="panel-input" multiple>
                </div>
                <div class="grid gap-2">
                    <label class="flex items-center gap-3 text-sm font-medium text-slate-700">
                        <input type="hidden" name="status" value="0">
                        <input type="checkbox" name="status" value="1" class="rounded border-slate-300 text-rose-500" @checked(old('status', $event->status ?? true))>
                        Active in listings
                    </label>
                    <label class="flex items-center gap-3 text-sm font-medium text-slate-700">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300 text-rose-500" @checked(old('is_featured', $event->is_featured ?? false))>
                        Mark as featured
                    </label>
                </div>
            </div>
        </div>

        @if (isset($event))
            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Current Gallery</h3>
                <div class="mt-4 grid gap-4">
                    @forelse ($event->galleryImages as $image)
                        <img src="{{ asset('storage/'.$image->image_path) }}" alt="" class="h-28 w-full rounded-2xl object-cover">
                    @empty
                        <p class="text-sm text-slate-500">No gallery uploaded yet.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit" class="panel-btn">{{ $buttonText }}</button>
    <a href="{{ route('admin.events.index') }}" class="panel-btn-secondary">Cancel</a>
</div>
