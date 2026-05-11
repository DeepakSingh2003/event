<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-rose-500">Overview</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Entertainment Control Centre</h2>
                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Monitor bookings, revenue, and show readiness across Indian cities from a cleaner admin experience.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-[24px] border border-slate-200/80 bg-white/80 px-4 py-4 shadow-sm">
                    <p class="text-[11px] uppercase tracking-[0.3em] text-slate-400">Focus Markets</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">Mumbai, Delhi NCR, Bengaluru</p>
                </div>
                <div class="rounded-[24px] bg-slate-900 px-4 py-4 text-white shadow-[0_24px_60px_-40px_rgba(15,23,42,0.9)]">
                    <p class="text-[11px] uppercase tracking-[0.3em] text-slate-400">Currency & Tax</p>
                    <p class="mt-2 text-sm font-semibold">INR pricing with GST-ready reporting</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-12">
        <div class="panel-stat xl:col-span-2">
            <div>
                <p class="text-sm text-slate-500">Published Events</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight text-slate-950">{{ $stats['events'] }}</p>
            </div>
            <p class="text-sm text-slate-400">Titles currently live for discovery and sales.</p>
        </div>

        <div class="panel-stat xl:col-span-2">
            <div>
                <p class="text-sm text-slate-500">Total Bookings</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight text-slate-950">{{ $stats['bookings'] }}</p>
            </div>
            <p class="text-sm text-slate-400">All confirmed and active reservations in the system.</p>
        </div>

        <div class="panel-stat xl:col-span-2">
            <div>
                <p class="text-sm text-slate-500">Cancelled</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight text-slate-950">{{ $stats['cancelled'] }}</p>
            </div>
            <p class="text-sm text-slate-400">Bookings that need refund or reallocation attention.</p>
        </div>

        <div class="panel-stat xl:col-span-2">
            <div>
                <p class="text-sm text-slate-500">Failed Payments</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight text-slate-950">{{ $stats['failed_payments'] }}</p>
            </div>
            <p class="text-sm text-slate-400">Transactions that may require manual follow-up.</p>
        </div>

        <div class="panel-stat relative overflow-hidden bg-slate-950 text-white xl:col-span-4">
            <div class="pointer-events-none absolute -right-14 -top-10 h-40 w-40 rounded-full bg-rose-500/20 blur-3xl"></div>
            <div class="relative">
                <p class="text-sm text-slate-300">Gross Revenue</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight text-white sm:text-5xl">{{ \App\Support\Currency::compactInr($stats['revenue']) }}</p>
            </div>
            <div class="relative text-sm text-slate-300">
                <p>Full total: {{ \App\Support\Currency::inr($stats['revenue']) }}</p>
                <p class="mt-2">Captured across online and manual settlements.</p>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="panel-card p-6">
            <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-slate-950">Daily Revenue</h3>
                    <p class="mt-1 text-sm text-slate-500">Last 30 days of booking collections.</p>
                </div>
                <span class="panel-chip border border-rose-100 bg-rose-50 text-rose-600">Daily Trend</span>
            </div>
            <div class="h-80">
                <canvas id="dailyBookingsChart" class="!h-full !w-full"></canvas>
            </div>
        </div>

        <div class="panel-card p-6">
            <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-slate-950">Monthly Revenue</h3>
                    <p class="mt-1 text-sm text-slate-500">Six-month sales performance in INR.</p>
                </div>
                <span class="panel-chip border border-slate-200 bg-slate-100 text-slate-700">Monthly Trend</span>
            </div>
            <div class="h-80">
                <canvas id="monthlyBookingsChart" class="!h-full !w-full"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="panel-card p-6 xl:col-span-2">
            <div class="mb-6 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-semibold text-slate-950">Recent Bookings</h3>
                    <p class="mt-1 text-sm text-slate-500">Latest reservations placed across the platform.</p>
                </div>
                <a href="{{ route('admin.bookings.index') }}" class="text-sm font-semibold text-rose-500">View all</a>
            </div>

            <div class="overflow-x-auto">
                <table class="panel-table">
                    <thead class="text-left text-xs uppercase tracking-[0.24em] text-slate-400">
                        <tr>
                            <th class="pb-4">Reference</th>
                            <th class="pb-4">Customer</th>
                            <th class="pb-4">Event</th>
                            <th class="pb-4">Amount</th>
                            <th class="pb-4">Booking</th>
                            <th class="pb-4">Payment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recentBookings as $booking)
                            @php
                                $bookingStatusClass = match ($booking->status) {
                                    'confirmed' => 'bg-emerald-100 text-emerald-700',
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'cancelled' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };

                                $paymentStatusClass = match ($booking->payment_status) {
                                    'paid' => 'bg-emerald-100 text-emerald-700',
                                    'failed' => 'bg-rose-100 text-rose-700',
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp

                            <tr class="transition hover:bg-slate-50/80">
                                <td class="py-4 font-semibold text-slate-950">{{ $booking->booking_reference }}</td>
                                <td class="py-4">
                                    <p class="font-medium text-slate-900">{{ $booking->user->name }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.2em] text-slate-400">{{ $booking->seats }} seats</p>
                                </td>
                                <td class="py-4">
                                    <p class="font-medium text-slate-900">{{ $booking->event->title }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $booking->show->venue->city }}</p>
                                </td>
                                <td class="py-4 font-medium text-slate-900">{{ \App\Support\Currency::inr($booking->total_amount) }}</td>
                                <td class="py-4">
                                    <span class="panel-badge {{ $bookingStatusClass }}">{{ $booking->status }}</span>
                                </td>
                                <td class="py-4">
                                    <span class="panel-badge {{ $paymentStatusClass }}">{{ $booking->payment_status }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel-card p-6">
                <div class="mb-4">
                    <h3 class="text-xl font-semibold text-slate-950">Top Events</h3>
                    <p class="mt-1 text-sm text-slate-500">Highest performing titles by bookings.</p>
                </div>
                <div class="space-y-3">
                    @forelse ($report['topEvents'] as $event)
                        <div class="flex items-center justify-between rounded-[22px] border border-slate-100 bg-slate-50/80 px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-900">{{ $event->title }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $event->eventCategory?->name ?? $event->category }}</p>
                            </div>
                            <span class="text-sm font-semibold text-slate-500">{{ $event->bookings_count }} bookings</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No booking data available yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel-card p-6">
                <div class="mb-4">
                    <h3 class="text-xl font-semibold text-slate-950">Upcoming Shows</h3>
                    <p class="mt-1 text-sm text-slate-500">Next live inventory windows to keep an eye on.</p>
                </div>
                <div class="space-y-3">
                    @forelse ($upcomingShows as $show)
                        <div class="rounded-[22px] border border-slate-100 bg-white px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-950">{{ $show->event->title }}</p>
                                    <p class="mt-2 text-sm text-slate-500">{{ $show->show_date->format('d M Y') }} at {{ \Carbon\Carbon::parse($show->show_time)->format('h:i A') }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $show->venue->name }}, {{ $show->venue->city }}</p>
                                </div>
                                <span class="panel-chip border border-slate-200 bg-slate-50 text-slate-700">{{ \App\Support\Currency::inr($show->price, 0) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No upcoming shows available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="panel-card p-6">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-slate-950">Notification Panel</h3>
                    <p class="mt-1 text-sm text-slate-500">Fresh system alerts and booking updates.</p>
                </div>
                <a href="{{ route('admin.notifications.index') }}" class="text-sm font-semibold text-rose-500">Open</a>
            </div>
            <div class="space-y-3">
                @forelse ($notifications as $notification)
                    <div class="rounded-[22px] border border-slate-100 px-4 py-4">
                        <p class="font-semibold text-slate-950">{{ $notification->data['title'] ?? 'Update' }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">{{ $notification->data['message'] ?? '' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No notifications yet.</p>
                @endforelse
            </div>
        </div>

        <div class="panel-card p-6">
            <div class="mb-4">
                <h3 class="text-xl font-semibold text-slate-950">Failed Payment Tracking</h3>
                <p class="mt-1 text-sm text-slate-500">Transactions that need recovery action.</p>
            </div>
            <div class="space-y-3">
                @forelse ($paymentAlerts as $log)
                    <div class="rounded-[22px] border border-rose-100 bg-rose-50 px-4 py-4">
                        <p class="font-semibold text-slate-950">{{ $log->booking?->booking_reference ?? 'System Log' }}</p>
                        <p class="mt-2 text-sm text-rose-700">{{ $log->gateway }} {{ $log->action }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No failed payment alerts.</p>
                @endforelse
            </div>
        </div>

        <div class="panel-card p-6">
            <div class="mb-4">
                <h3 class="text-xl font-semibold text-slate-950">Pending Refunds</h3>
                <p class="mt-1 text-sm text-slate-500">Recently initiated refund requests.</p>
            </div>
            <div class="space-y-3">
                @forelse ($recentRefunds as $refund)
                    <div class="rounded-[22px] border border-slate-100 px-4 py-4">
                        <p class="font-semibold text-slate-950">{{ $refund->booking?->booking_reference }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $refund->booking?->event?->title }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ \App\Support\Currency::inr($refund->amount) }} | {{ $refund->status }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No refunds processed yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="panel-card p-6">
            <div class="mb-4">
                <h3 class="text-xl font-semibold text-slate-950">Most Active Users</h3>
                <p class="mt-1 text-sm text-slate-500">Customers driving the most repeat demand.</p>
            </div>
            <div class="space-y-3">
                @forelse ($report['activeUsers'] as $user)
                    <div class="flex items-center justify-between rounded-[22px] border border-slate-100 bg-slate-50/80 px-4 py-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-950">{{ $user->name }}</p>
                            <p class="mt-1 truncate text-sm text-slate-500">{{ $user->email }}</p>
                        </div>
                        <span class="text-sm font-semibold text-slate-500">{{ $user->bookings_count }} bookings</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No active users yet.</p>
                @endforelse
            </div>
        </div>

        <div class="panel-card p-6">
            <div class="mb-4">
                <h3 class="text-xl font-semibold text-slate-950">AI Suggestions</h3>
                <p class="mt-1 text-sm text-slate-500">Recommended event mix based on recent booking behaviour.</p>
            </div>
            <div class="space-y-3">
                @forelse ($recommendations as $event)
                    <div class="rounded-[22px] border border-slate-100 px-4 py-4">
                        <p class="font-semibold text-slate-950">{{ $event->title }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $event->eventCategory?->name ?? $event->category }} | {{ $event->language }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $event->shows->first()?->venue?->city ?? 'Multiple cities' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Not enough booking history yet for personalised recommendations.</p>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const inrFormatter = new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0,
        });

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            const value = context.parsed.y ?? context.parsed;

                            return `Revenue: ${inrFormatter.format(value ?? 0)}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                    border: {
                        display: false,
                    },
                    ticks: {
                        color: '#64748b',
                    },
                },
                y: {
                    border: {
                        display: false,
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.18)',
                    },
                    ticks: {
                        color: '#64748b',
                        callback(value) {
                            return inrFormatter.format(value);
                        },
                    },
                },
            },
        };

        new Chart(document.getElementById('dailyBookingsChart'), {
            type: 'line',
            data: {
                labels: @json($report['dailyRevenue']->pluck('label')),
                datasets: [{
                    data: @json($report['dailyRevenue']->pluck('total')),
                    borderColor: '#f43f5e',
                    backgroundColor: 'rgba(244, 63, 94, 0.15)',
                    pointBackgroundColor: '#f43f5e',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 5,
                    fill: true,
                    tension: 0.4,
                }],
            },
            options: chartOptions,
        });

        new Chart(document.getElementById('monthlyBookingsChart'), {
            type: 'bar',
            data: {
                labels: @json($report['monthlyRevenue']->pluck('label')),
                datasets: [{
                    data: @json($report['monthlyRevenue']->pluck('total')),
                    backgroundColor: ['#0f172a', '#1e293b', '#334155', '#475569', '#64748b', '#f43f5e'],
                    borderRadius: 18,
                    borderSkipped: false,
                }],
            },
            options: chartOptions,
        });
    </script>
</x-app-layout>
