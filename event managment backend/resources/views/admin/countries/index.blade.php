<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Countries</p>
            <h2 class="text-3xl font-semibold text-slate-900">Country Management</h2>
        </div>
    </x-slot>

    <div class="panel-card p-6">
        <form action="{{ route('admin.countries.store') }}" method="POST" class="grid gap-4 md:grid-cols-4">
            @csrf
            <input type="text" name="name" class="panel-input" placeholder="Country name" required>
            <input type="text" name="iso_code" class="panel-input" placeholder="ISO code e.g. IN">
            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-rose-500" checked>
                Active
            </label>
            <button class="panel-btn" type="submit">Add Country</button>
        </form>
    </div>

    <div class="panel-card mt-6 overflow-hidden">
        <table class="panel-table">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-4">Country</th>
                    <th class="px-6 py-4">ISO</th>
                    <th class="px-6 py-4">Cities</th>
                    <th class="px-6 py-4">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($countries as $country)
                    <tr>
                        <td class="px-6 py-4 font-semibold">{{ $country->name }}</td>
                        <td class="px-6 py-4">{{ $country->iso_code ?: '-' }}</td>
                        <td class="px-6 py-4">{{ $country->cities_count }}</td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.countries.destroy', $country) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="panel-btn-secondary" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-6 py-4">{{ $countries->links() }}</div>
    </div>
</x-app-layout>
