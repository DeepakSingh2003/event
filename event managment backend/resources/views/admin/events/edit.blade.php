<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Events</p>
            <h2 class="text-3xl font-semibold text-slate-900">Edit {{ $event->title }}</h2>
        </div>
    </x-slot>

    <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.events._form', ['buttonText' => 'Update Event Listing'])
    </form>
</x-app-layout>
