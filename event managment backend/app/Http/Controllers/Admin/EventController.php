<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Tag;
use App\Models\Venue;
use App\Services\ActivityLogService;
use App\Services\SeatLayoutService;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        private readonly SlugService $slugService,
        private readonly ActivityLogService $activityLogService,
        private readonly SeatLayoutService $seatLayoutService,
    ) {
    }

    public function index(): View
    {
        $events = Event::query()
            ->with(['eventCategory', 'tags', 'primaryShow.venue.cityRecord'])
            ->withCount(['shows', 'bookings'])
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when(request('category_id'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when(request('publication_status'), fn ($query, $status) => $query->where('publication_status', $status))
            ->when(request()->filled('featured'), fn ($query) => $query->where('is_featured', request()->boolean('featured')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get();

        return view('admin.events.index', compact('events', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();
        $tags = Tag::query()->orderBy('name')->get();
        $venues = Venue::query()->with('cityRecord')->whereNotNull('city_id')->orderBy('name')->get();

        return view('admin.events.create', compact('categories', 'tags', 'venues'));
    }

    public function store(Request $request): RedirectResponse
    {
        $eventData = $this->validatedEventData($request);
        $listingData = $this->validatedListingData($request);

        $event = DB::transaction(function () use ($request, $eventData, $listingData) {
            $payload = $eventData;
            $payload['slug'] = $this->slugService->generate(Event::class, $payload['slug'] ?: $payload['title']);
            $payload['poster_image'] = $request->hasFile('poster_image') ? $request->file('poster_image')->store('events/posters', 'public') : null;
            $payload['banner_image'] = $request->hasFile('banner_image') ? $request->file('banner_image')->store('events/banners', 'public') : null;
            $payload['status'] = $request->boolean('status', true);
            $payload['is_featured'] = $request->boolean('is_featured');

            $event = Event::create(Arr::except($payload, ['tag_ids', 'gallery_images', 'timeline']));
            $event->tags()->sync($request->input('tag_ids', []));
            $this->syncGallery($request, $event);
            $this->syncTimeline($request, $event);
            $this->syncPrimaryListing($event, $listingData);

            return $event;
        });

        $this->activityLogService->log($request->user(), 'event.created', $event, 'Event created.');

        return redirect()->route('admin.events.index')->with('success', 'Event listing created successfully.');
    }

    public function show(Event $event): View
    {
        $event->load([
            'eventCategory',
            'tags',
            'galleryImages',
            'timelines',
            'primaryShow.venue.cityRecord',
            'shows.venue.cityRecord',
            'bookings.user',
        ]);

        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();
        $tags = Tag::query()->orderBy('name')->get();
        $venues = Venue::query()->with('cityRecord')->whereNotNull('city_id')->orderBy('name')->get();
        $event->load(['tags', 'galleryImages', 'timelines', 'primaryShow.venue.cityRecord']);

        return view('admin.events.edit', compact('event', 'categories', 'tags', 'venues'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $eventData = $this->validatedEventData($request);
        $listingData = $this->validatedListingData($request);

        DB::transaction(function () use ($request, $event, $eventData, $listingData) {
            $payload = $eventData;
            $payload['slug'] = $this->slugService->generate(Event::class, $payload['slug'] ?: $payload['title'], $event->id);
            $payload['status'] = $request->boolean('status', true);
            $payload['is_featured'] = $request->boolean('is_featured');

            if ($request->hasFile('poster_image')) {
                $payload['poster_image'] = $request->file('poster_image')->store('events/posters', 'public');
            }

            if ($request->hasFile('banner_image')) {
                $payload['banner_image'] = $request->file('banner_image')->store('events/banners', 'public');
            }

            $event->update(Arr::except($payload, ['tag_ids', 'gallery_images', 'timeline']));
            $event->tags()->sync($request->input('tag_ids', []));
            $this->syncGallery($request, $event);
            $this->syncTimeline($request, $event);
            $this->syncPrimaryListing($event, $listingData);
        });

        $this->activityLogService->log($request->user(), 'event.updated', $event, 'Event updated.');

        return redirect()->route('admin.events.index')->with('success', 'Event listing updated successfully.');
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $event->delete();

        $this->activityLogService->log($request->user(), 'event.deleted', $event, 'Event deleted.');

        return redirect()->route('admin.events.index')->with('success', 'Event deleted successfully.');
    }

    private function validatedEventData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'slug' => ['nullable', 'string', 'max:255'],
            'poster_image' => ['nullable', 'image', 'max:2048'],
            'banner_image' => ['nullable', 'image', 'max:4096'],
            'language' => ['required', 'string', 'max:100'],
            'status' => ['nullable', 'boolean'],
            'publication_status' => ['required', 'in:draft,published,cancelled'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:4096'],
            'timeline' => ['nullable', 'array'],
            'timeline.*.title' => ['required_with:timeline', 'nullable', 'string', 'max:255'],
            'timeline.*.description' => ['nullable', 'string'],
            'timeline.*.starts_at' => ['nullable', 'date'],
            'timeline.*.ends_at' => ['nullable', 'date'],
        ]);
    }

    private function validatedListingData(Request $request): array
    {
        $data = $request->validate([
            'listing_venue_id' => ['required', 'exists:venues,id'],
            'listing_show_date' => ['required', 'date'],
            'listing_show_time' => ['required', 'date_format:H:i'],
            'listing_price' => ['required', 'numeric', 'min:0'],
            'listing_currency_code' => ['required', 'in:INR,USD,EUR,GBP,SGD,AED,BRL'],
            'listing_status' => ['required', 'in:scheduled,cancelled,sold_out'],
            'listing_booking_open_at' => ['nullable', 'date'],
            'listing_booking_close_at' => ['nullable', 'date', 'after_or_equal:listing_booking_open_at'],
            'listing_seat_lock_minutes' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        return [
            'venue_id' => $data['listing_venue_id'],
            'show_date' => $data['listing_show_date'],
            'show_time' => $data['listing_show_time'],
            'price' => $data['listing_price'],
            'currency_code' => $data['listing_currency_code'],
            'status' => $data['listing_status'],
            'booking_open_at' => $data['listing_booking_open_at'] ?? null,
            'booking_close_at' => $data['listing_booking_close_at'] ?? null,
            'seat_lock_minutes' => $data['listing_seat_lock_minutes'],
        ];
    }

    private function syncGallery(Request $request, Event $event): void
    {
        if (! $request->hasFile('gallery_images')) {
            return;
        }

        $event->galleryImages()->delete();

        foreach ($request->file('gallery_images') as $index => $file) {
            $event->galleryImages()->create([
                'image_path' => $file->store('events/gallery', 'public'),
                'caption' => $request->input("gallery_captions.{$index}"),
                'sort_order' => $index,
            ]);
        }
    }

    private function syncTimeline(Request $request, Event $event): void
    {
        $event->timelines()->delete();

        collect($request->input('timeline', []))
            ->filter(fn ($item) => filled($item['title'] ?? null))
            ->each(function (array $item, int $index) use ($event) {
                $event->timelines()->create([
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'starts_at' => $item['starts_at'] ?? null,
                    'ends_at' => $item['ends_at'] ?? null,
                    'sort_order' => $index,
                ]);
            });
    }

    private function syncPrimaryListing(Event $event, array $listingData): void
    {
        $primaryShow = $event->primaryShow()->first();

        if (! $primaryShow) {
            $show = $event->shows()->create([
                ...$listingData,
                'available_seats' => 0,
            ]);

            $this->seatLayoutService->generateForShow($show->load('venue'));

            return;
        }

        $venueChanged = (int) $primaryShow->venue_id !== (int) $listingData['venue_id'];
        $priceChanged = (float) $primaryShow->price !== (float) $listingData['price'];
        $currencyChanged = ($primaryShow->currency_code ?? 'INR') !== $listingData['currency_code'];

        if ($venueChanged && $primaryShow->bookings()->exists()) {
            throw ValidationException::withMessages([
                'listing_venue_id' => 'You cannot change the venue on a ticket listing that already has bookings.',
            ]);
        }

        $primaryShow->update($listingData);
        $primaryShow->load('venue');

        if (! $primaryShow->seats()->exists() || $venueChanged) {
            $this->seatLayoutService->generateForShow($primaryShow, true);

            return;
        }

        if ($priceChanged || $currencyChanged) {
            $this->seatLayoutService->syncPricingForShow($primaryShow);
        }
    }
}
