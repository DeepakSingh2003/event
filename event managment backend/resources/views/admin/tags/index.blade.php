<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Tags</p>
            <h2 class="text-3xl font-semibold text-slate-900">Tag Management</h2>
        </div>
    </x-slot>

    <div class="panel-card p-6">
        <form action="{{ route('admin.tags.store') }}" method="POST" class="flex gap-3">
            @csrf
            <input type="text" name="name" class="panel-input" placeholder="Add tag" required>
            <button class="panel-btn" type="submit">Save</button>
        </form>
    </div>

    <div class="panel-card mt-6 overflow-hidden">
        <table class="panel-table">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-4">Tag</th>
                    <th class="px-6 py-4">Events</th>
                    <th class="px-6 py-4">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($tags as $tag)
                    <tr>
                        <td class="px-6 py-4 font-semibold">{{ $tag->name }}</td>
                        <td class="px-6 py-4">{{ $tag->events_count }}</td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="panel-btn-secondary" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-6 py-4">{{ $tags->links() }}</div>
    </div>
</x-app-layout>
