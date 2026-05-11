<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Users</p>
            <h2 class="text-3xl font-semibold text-slate-900">Advanced User Management</h2>
        </div>
    </x-slot>

    <div class="panel-card p-6">
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <input type="text" name="search" value="{{ request('search') }}" class="panel-input" placeholder="Search name or email">
            <select name="role" class="panel-select">
                <option value="">All roles</option>
                @foreach (['admin', 'manager', 'user'] as $role)
                    <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
            <select name="blocked" class="panel-select">
                <option value="">Blocked or not</option>
                <option value="1" @selected(request('blocked') === '1')>Blocked</option>
                <option value="0" @selected(request('blocked') === '0')>Active</option>
            </select>
            <button class="panel-btn" type="submit">Filter</button>
        </form>
    </div>

    <div class="panel-card mt-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="panel-table">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Bookings</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-6 py-4 font-semibold">{{ $user->name }}</td>
                            <td class="px-6 py-4">{{ $user->email }}</td>
                            <td class="px-6 py-4">{{ ucfirst($user->role) }}</td>
                            <td class="px-6 py-4">{{ $user->bookings_count }}</td>
                            <td class="px-6 py-4">
                                <span class="panel-badge {{ $user->is_blocked ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $user->is_blocked ? 'Blocked' : 'Active' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.users.show', $user) }}" class="panel-btn-secondary">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $users->links() }}</div>
    </div>
</x-app-layout>
