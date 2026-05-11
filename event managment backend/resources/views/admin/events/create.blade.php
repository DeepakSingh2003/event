<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Events</p>
            <h2 class="text-3xl font-semibold text-slate-900">Create Event Listing</h2>
        </div>
    </x-slot>

    <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
        @include('admin.events._form', ['buttonText' => 'Save Event Listing'])
    </form>
</x-app-layout>
