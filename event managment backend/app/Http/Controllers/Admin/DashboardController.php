<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\PaymentLog;
use App\Models\Refund;
use App\Models\Show;
use App\Services\RecommendationService;
use App\Services\ReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
        private readonly RecommendationService $recommendationService,
    ) {
    }

    public function __invoke(): View
    {
        $report = $this->reportService->metrics([]);
        $stats = [
            'events' => Event::count(),
            'bookings' => $report['bookings'],
            'revenue' => $report['revenue'],
            'failed_payments' => $report['failed_payments'],
            'cancelled' => $report['cancelled'],
        ];

        $recentBookings = Booking::with(['user', 'event', 'show'])
            ->latest('booked_at')
            ->take(8)
            ->get();

        $upcomingShows = Show::with(['event', 'venue'])
            ->whereDate('show_date', '>=', now()->toDateString())
            ->orderBy('show_date')
            ->orderBy('show_time')
            ->take(5)
            ->get();

        $notifications = auth()->user()->notifications()->latest()->take(5)->get();
        $recommendations = $this->recommendationService->forUser(auth()->user(), 4);
        $recentRefunds = Refund::with('booking.event')->latest()->take(5)->get();
        $paymentAlerts = PaymentLog::with('booking.event')->where('status', 'failed')->latest()->take(5)->get();

        return view('dashboard', compact(
            'stats',
            'report',
            'recentBookings',
            'upcomingShows',
            'notifications',
            'recommendations',
            'recentRefunds',
            'paymentAlerts'
        ));
    }
}
