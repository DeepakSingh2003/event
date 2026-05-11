<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function filters(array $input): array
    {
        return [
            'from' => $input['from'] ?? now()->subDays(30)->toDateString(),
            'to' => $input['to'] ?? now()->toDateString(),
            'city_id' => $input['city_id'] ?? null,
            'event_id' => $input['event_id'] ?? null,
        ];
    }

    public function metrics(array $input): array
    {
        $filters = $this->filters($input);
        $baseQuery = $this->bookingsQuery($filters);

        return [
            'filters' => $filters,
            'revenue' => (clone $baseQuery)->where('bookings.status', 'confirmed')->sum('bookings.total_amount'),
            'bookings' => (clone $baseQuery)->count(),
            'cancelled' => (clone $baseQuery)->where('bookings.status', 'cancelled')->count(),
            'failed_payments' => (clone $baseQuery)->where('bookings.payment_status', 'failed')->count(),
            'dailyRevenue' => $this->dailyRevenue($filters),
            'monthlyRevenue' => $this->monthlyRevenue($filters),
            'topEvents' => $this->topEvents($filters),
            'activeUsers' => $this->activeUsers($filters),
        ];
    }

    public function dailyRevenue(array $filters): Collection
    {
        return $this->bookingsQuery($filters)
            ->selectRaw('DATE(bookings.booked_at) as label, SUM(bookings.total_amount) as total')
            ->where('bookings.status', 'confirmed')
            ->groupBy('label')
            ->orderBy('label')
            ->get();
    }

    public function monthlyRevenue(array $filters): Collection
    {
        return $this->bookingsQuery($filters)
            ->selectRaw("DATE_FORMAT(bookings.booked_at, '%b %Y') as label, DATE_FORMAT(bookings.booked_at, '%Y-%m-01') as sort_date, SUM(bookings.total_amount) as total")
            ->where('bookings.status', 'confirmed')
            ->groupBy('label', 'sort_date')
            ->orderBy('sort_date')
            ->get();
    }

    public function topEvents(array $filters): Collection
    {
        return Event::query()
            ->leftJoin('bookings', 'events.id', '=', 'bookings.event_id')
            ->leftJoin('shows', 'bookings.show_id', '=', 'shows.id')
            ->leftJoin('venues', 'shows.venue_id', '=', 'venues.id')
            ->when($filters['from'], fn ($query) => $query->whereDate('bookings.booked_at', '>=', $filters['from']))
            ->when($filters['to'], fn ($query) => $query->whereDate('bookings.booked_at', '<=', $filters['to']))
            ->when($filters['city_id'], fn ($query) => $query->where('venues.city_id', $filters['city_id']))
            ->select('events.id', 'events.title', DB::raw('COUNT(bookings.id) as bookings_count'))
            ->groupBy('events.id', 'events.title')
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get();
    }

    public function activeUsers(array $filters): Collection
    {
        return User::query()
            ->join('bookings', 'users.id', '=', 'bookings.user_id')
            ->leftJoin('shows', 'bookings.show_id', '=', 'shows.id')
            ->leftJoin('venues', 'shows.venue_id', '=', 'venues.id')
            ->when($filters['from'], fn ($query) => $query->whereDate('bookings.booked_at', '>=', $filters['from']))
            ->when($filters['to'], fn ($query) => $query->whereDate('bookings.booked_at', '<=', $filters['to']))
            ->when($filters['city_id'], fn ($query) => $query->where('venues.city_id', $filters['city_id']))
            ->when($filters['event_id'], fn ($query) => $query->where('bookings.event_id', $filters['event_id']))
            ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(bookings.id) as bookings_count'))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get();
    }

    public function bookingsQuery(array $filters): Builder
    {
        return Booking::query()
            ->leftJoin('shows', 'bookings.show_id', '=', 'shows.id')
            ->leftJoin('venues', 'shows.venue_id', '=', 'venues.id')
            ->when($filters['from'], fn ($query) => $query->whereDate('bookings.booked_at', '>=', $filters['from']))
            ->when($filters['to'], fn ($query) => $query->whereDate('bookings.booked_at', '<=', $filters['to']))
            ->when($filters['city_id'], fn ($query) => $query->where('venues.city_id', $filters['city_id']))
            ->when($filters['event_id'], fn ($query) => $query->where('bookings.event_id', $filters['event_id']));
    }
}
