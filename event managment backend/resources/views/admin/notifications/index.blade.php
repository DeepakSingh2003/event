<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <!-- Left Side -->
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">
                Notifications
            </p>
            <h2 class="text-3xl font-semibold text-slate-900">
                Notification Center
            </h2>
        </div>

        <!-- Right Side Button -->
        <form action="{{ route('admin.notifications.read-all') }}" method="POST">
            @csrf
            <button class="panel-btn-secondary" type="submit">
                Mark All Read
            </button>
        </form>

    </div>
</x-slot>

    <div class="space-y-4">
        @foreach ($notifications as $notification)
            <div class="panel-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-semibold">{{ $notification->data['title'] ?? 'System Notification' }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $notification->data['message'] ?? '' }}</p>
                        <p class="mt-2 text-xs uppercase tracking-[0.3em] text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if (! $notification->read_at)
                        <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST">
                            @csrf
                            <button class="panel-btn-secondary" type="submit">Mark Read</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
        <div>{{ $notifications->links() }}</div>
    </div>
</x-app-layout>
