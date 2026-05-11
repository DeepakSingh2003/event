<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ data_get($appSettings ?? [], 'general.site_name', config('app.name', 'Laravel')) }} Admin</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @php
            $siteName = data_get($appSettings ?? [], 'general.site_name', config('app.name', 'Laravel'));
            $nowInIndia = now()->timezone('Asia/Kolkata');
            $unreadNotifications = auth()->user()->unreadNotifications()->count();
            $navItems = [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
                ['label' => 'Event Catalog', 'route' => 'admin.events.index', 'active' => 'admin.events.*'],
                ['label' => 'Ticket Dates', 'route' => 'admin.shows.index', 'active' => 'admin.shows.*'],
                ['label' => 'Seat Types', 'route' => 'admin.seat-types.index', 'active' => 'admin.seat-types.*'],
                ['label' => 'Categories', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*'],
                ['label' => 'Tags', 'route' => 'admin.tags.index', 'active' => 'admin.tags.*'],
                ['label' => 'Countries', 'route' => 'admin.countries.index', 'active' => 'admin.countries.*'],
                ['label' => 'Cities', 'route' => 'admin.cities.index', 'active' => 'admin.cities.*'],
                ['label' => 'Venues', 'route' => 'admin.venues.index', 'active' => 'admin.venues.*'],
                ['label' => 'Ticket Orders', 'route' => 'admin.bookings.index', 'active' => 'admin.bookings.*'],
                ['label' => 'Offers', 'route' => 'admin.coupons.index', 'active' => 'admin.coupons.*'],
                ['label' => 'Reports', 'route' => 'admin.reports.index', 'active' => 'admin.reports.*'],
                ['label' => 'Alerts', 'route' => 'admin.notifications.index', 'active' => 'admin.notifications.*'],
                ['label' => 'Payment Logs', 'route' => 'admin.payment-logs.index', 'active' => 'admin.payment-logs.*'],
                ['label' => 'Customers', 'route' => 'admin.users.index', 'active' => 'admin.users.*'],
                ['label' => 'Settings', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'],
                ['label' => 'Profile', 'route' => 'profile.edit', 'active' => 'profile.*'],
            ];
        @endphp

        <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(251,113,133,0.16),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(14,116,144,0.12),_transparent_24%),linear-gradient(180deg,#fff7ed_0%,#f8fafc_40%,#eef2ff_100%)]">
            <div class="flex min-h-screen flex-col xl:flex-row">
                <aside class="w-full shrink-0 border-b border-slate-800/80 bg-slate-950 text-white xl:sticky xl:top-0 xl:h-screen xl:w-[18.5rem] xl:border-b-0 xl:border-r">
                    <div class="flex h-full flex-col overflow-y-auto">
                        <div class="px-6 pb-4 pt-6">
                           
                        </div>

                        <div class="px-4">
                            <!-- <p class="px-4 text-[11px] uppercase tracking-[0.34em] text-slate-500">Navigation</p> -->
                            <nav class="mt-3 space-y-1">
                                @foreach ($navItems as $item)
                                    @continue(($item['route'] === 'admin.users.index' || $item['route'] === 'admin.settings.index') && !auth()->user()->isAdmin())
                                    <a
                                        href="{{ route($item['route']) }}"
                                        class="panel-nav-link {{ request()->routeIs($item['active']) ? 'panel-nav-link-active' : 'panel-nav-link-inactive' }}"
                                    >
                                        <span>{{ $item['label'] }}</span>
                                        @if (request()->routeIs($item['active']))
                                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                        @endif
                                    </a>
                                @endforeach
                            </nav>
                        </div>

                        <div class="mt-auto px-6 pb-6 pt-8">
                            <div class="rounded-[28px] border border-white/10 bg-white/5 p-4 text-sm text-slate-300">
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Signed In</p>
                                <p class="mt-3 text-base font-semibold text-white">{{ auth()->user()->name }}</p>
                                <p class="text-xs uppercase tracking-[0.28em] text-slate-400">{{ auth()->user()->role }}</p>
                                <form action="{{ route('logout') }}" method="POST" class="mt-5">
                                    @csrf
                                    <button type="submit" class="panel-btn w-full bg-rose-500 hover:bg-rose-400">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="flex-1">
                    <header class="border-b border-slate-200/70 bg-white/80 backdrop-blur-xl">
                        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h1 class="text-xl font-semibold text-slate-900">{{ $siteName }} Ticketing Admin</h1>
                                    <span class="panel-chip border border-slate-900 bg-slate-900 text-white">{{ $nowInIndia->format('d M Y') }} | {{ $nowInIndia->format('h:i A') }} IST</span>
                                </div>

                                <a href="{{ route('admin.notifications.index') }}" class="panel-chip border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
                                    Notifications
                                    @if ($unreadNotifications)
                                        <span class="rounded-full bg-rose-500 px-2 py-0.5 text-xs text-white">{{ $unreadNotifications }}</span>
                                    @endif
                                </a>
                            </div>

                            @if (isset($header))
                                <div class="mt-6">
                                    {{ $header }}
                                </div>
                            @endif
                        </div>
                    </header>

                    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                        @if (session('success'))
                            <div x-data="toast" x-show="visible" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div x-data="toast" x-show="visible" class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                {{ session('error') }}
                            </div>
                        @endif

                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
