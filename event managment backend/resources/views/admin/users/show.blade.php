<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">User Details</p>
            <h2 class="text-3xl font-semibold text-slate-900">{{ $user->name }}</h2>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6">
            <div class="panel-card p-6">
                <div class="space-y-3 text-sm text-slate-600">
                    <p><span class="font-semibold text-slate-900">Email:</span> {{ $user->email }}</p>
                    <p><span class="font-semibold text-slate-900">Role:</span> {{ ucfirst($user->role) }}</p>
                    <p><span class="font-semibold text-slate-900">Status:</span> {{ $user->is_blocked ? 'Blocked' : 'Active' }}</p>
                    <p><span class="font-semibold text-slate-900">Last Active:</span> {{ optional($user->last_active_at)->format('d M Y h:i A') }}</p>
                </div>
            </div>

            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Actions</h3>
                <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <select name="role" class="panel-select">
                        @foreach (['admin', 'manager', 'user'] as $role)
                            <option value="{{ $role }}" @selected($user->role === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                    <button class="panel-btn w-full" type="submit">Update Role</button>
                </form>
                <form action="{{ route('admin.users.toggle-block', $user) }}" method="POST" class="mt-3">
                    @csrf
                    @method('PATCH')
                    <button class="panel-btn-secondary w-full" type="submit">{{ $user->is_blocked ? 'Unblock' : 'Block' }} User</button>
                </form>
            </div>
        </div>

        <div class="space-y-6 xl:col-span-2">
            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Booking History</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($user->bookings as $booking)
                        <div class="rounded-2xl border border-slate-100 px-4 py-3">
                            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="font-semibold">{{ $booking->event->title }}</p>
                                    <p class="text-sm text-slate-500">{{ $booking->booking_reference }} | {{ $booking->show->venue->name }}</p>
                                </div>
                                <span class="panel-badge bg-slate-100 text-slate-700">{{ $booking->status }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No bookings found.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Activity Logs</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($user->activityLogs as $log)
                        <div class="rounded-2xl border border-slate-100 px-4 py-3">
                            <p class="font-semibold">{{ $log->action }}</p>
                            <p class="text-sm text-slate-500">{{ $log->description }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No activity captured yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Recommended Events</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($recommendations as $event)
                        <div class="rounded-2xl border border-slate-100 px-4 py-3">
                            <p class="font-semibold">{{ $event->title }}</p>
                            <p class="text-sm text-slate-500">{{ $event->eventCategory?->name ?? $event->category }} | {{ $event->language }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No recommendations available yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
