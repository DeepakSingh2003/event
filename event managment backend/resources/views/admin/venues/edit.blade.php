<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Venues</p>
            <h2 class="text-3xl font-semibold text-slate-900">Edit Venue</h2>
        </div>
    </x-slot>

        <form action="{{ route('admin.venues.update', $venue) }}" method="POST" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.venues._form', ['buttonText' => 'Update Venue'])
    </form>
</x-app-layout>
