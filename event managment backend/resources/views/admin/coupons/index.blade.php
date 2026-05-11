<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Coupons</p>
            <h2 class="text-3xl font-semibold text-slate-900">Marketing & Promo Codes</h2>
        </div>
    </x-slot>

    <div class="panel-card p-6">
        <form action="{{ route('admin.coupons.store') }}" method="POST" class="grid gap-4 md:grid-cols-4">
            @csrf
            <input type="text" name="code" class="panel-input" placeholder="PROMO10" required>
            <select name="type" class="panel-select">
                <option value="fixed">Fixed</option>
                <option value="percentage">Percentage</option>
            </select>
            <input type="number" step="0.01" name="value" class="panel-input" placeholder="Value" required>
            <button class="panel-btn" type="submit">Create Coupon</button>
        </form>
    </div>

    <div class="panel-card mt-6 overflow-hidden">
        <table class="panel-table">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-4">Code</th>
                    <th class="px-6 py-4">Type</th>
                    <th class="px-6 py-4">Value</th>
                    <th class="px-6 py-4">Usage</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($coupons as $coupon)
                    <tr>
                        <td class="px-6 py-4 font-semibold">{{ $coupon->code }}</td>
                        <td class="px-6 py-4">{{ ucfirst($coupon->type) }}</td>
                        <td class="px-6 py-4">{{ $coupon->value }}</td>
                        <td class="px-6 py-4">{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? 'Unlimited' }}</td>
                        <td class="px-6 py-4">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="panel-btn-secondary" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-6 py-4">{{ $coupons->links() }}</div>
    </div>
</x-app-layout>
