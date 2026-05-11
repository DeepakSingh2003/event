<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Shows</p>
            <h2 class="text-3xl font-semibold text-slate-900">Create Show for {{ $event->title }}</h2>
        </div>
    </x-slot>

    <form action="{{ route('admin.events.shows.store', $event) }}" method="POST">
        @include('admin.shows._form', ['buttonText' => 'Save Show'])
    </form>
</x-app-layout>
