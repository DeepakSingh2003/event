<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Collection;

class RecommendationService
{
    public function forUser(User $user, int $limit = 6): Collection
    {
        $categoryIds = $user->bookings()
            ->where('status', 'confirmed')
            ->pluck('event_id')
            ->pipe(fn ($eventIds) => Event::query()->whereIn('id', $eventIds)->pluck('category_id'))
            ->filter()
            ->unique()
            ->values();

        $bookedEventIds = $user->bookings()->pluck('event_id');

        return Event::query()
            ->with(['eventCategory', 'tags', 'shows.venue'])
            ->where('publication_status', 'published')
            ->whereNotIn('id', $bookedEventIds)
            ->when($categoryIds->isNotEmpty(), fn ($query) => $query->whereIn('category_id', $categoryIds))
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->take($limit)
            ->get();
    }
}
