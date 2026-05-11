<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Categories</p>
            <h2 class="text-3xl font-semibold text-slate-900">Category Management</h2>
        </div>
    </x-slot>

    <div x-data="{ open: false }" class="space-y-6">
        <div class="flex items-center justify-between gap-4 panel-card p-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Frontend icon mapping</h3>
                <p class="mt-1 text-sm text-slate-500">Save the Lucide React component name here, like <span class="font-semibold text-slate-700">Ticket</span>, <span class="font-semibold text-slate-700">Music4</span>, <span class="font-semibold text-slate-700">Mic2</span>, or <span class="font-semibold text-slate-700">UtensilsCrossed</span>.</p>
            </div>
            <button class="panel-btn" @click="open = ! open" x-text="open ? 'Close Form' : 'Add Category'"></button>
        </div>

        <div x-show="open" x-transition class="panel-card p-6">
            <form action="{{ route('admin.categories.store') }}" method="POST" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @csrf
                <input type="text" name="name" class="panel-input" placeholder="Category name" value="{{ old('name') }}" required>
                <input type="text" name="icon" class="panel-input" placeholder="Lucide icon name" value="{{ old('icon') }}">
                <input type="text" name="description" class="panel-input xl:col-span-2" placeholder="Description" value="{{ old('description') }}">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-rose-500">
                    Active category
                </label>
                <div class="xl:col-span-3 flex justify-end">
                    <button class="panel-btn" type="submit">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <div class="panel-card mt-6 overflow-hidden">
        <table class="panel-table">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Lucide Icon</th>
                    <th class="px-6 py-4">Events</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($categories as $category)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $category->name }}</td>
                        <td class="px-6 py-4">
                            @if ($category->icon)
                                <code class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $category->icon }}</code>
                            @else
                                <span class="text-sm text-slate-400">Not set</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $category->events_count }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $category->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <details>
                                <summary class="cursor-pointer text-sm font-semibold text-slate-700">Edit</summary>
                                <div class="mt-4 rounded-3xl border border-slate-100 bg-slate-50 p-4">
                                    <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="grid gap-3 lg:grid-cols-4">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" class="panel-input" value="{{ $category->name }}" required>
                                        <input type="text" name="icon" class="panel-input" value="{{ $category->icon }}" placeholder="Lucide icon name">
                                        <input type="text" name="description" class="panel-input lg:col-span-2" value="{{ $category->description }}" placeholder="Description">
                                        <label class="flex items-center gap-2 text-sm text-slate-600">
                                            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-rose-500" @checked($category->is_active)>
                                            Active
                                        </label>
                                        <div class="lg:col-span-3 flex flex-wrap justify-end gap-2">
                                            <button class="panel-btn" type="submit">Update</button>
                                        </div>
                                    </form>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="mt-3 flex justify-end">
                                        @csrf
                                        @method('DELETE')
                                        <button class="panel-btn-secondary" type="submit">Delete</button>
                                    </form>
                                </div>
                            </details>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-6 py-4">{{ $categories->links() }}</div>
    </div>
</x-app-layout>