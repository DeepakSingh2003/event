<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Payments</p>
            <h2 class="text-3xl font-semibold text-slate-900">Payment Logs</h2>
        </div>
    </x-slot>

    <div class="panel-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="panel-table">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Gateway</th>
                        <th class="px-6 py-4">Action</th>
                        <th class="px-6 py-4">Booking</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($paymentLogs as $log)
                        <tr>
                            <td class="px-6 py-4 font-semibold">{{ $log->gateway }}</td>
                            <td class="px-6 py-4">{{ $log->action }}</td>
                            <td class="px-6 py-4">{{ $log->booking?->booking_reference }}</td>
                            <td class="px-6 py-4">{{ $log->status }}</td>
                            <td class="px-6 py-4">{{ \App\Support\Currency::inr($log->amount ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $paymentLogs->links() }}</div>
    </div>
</x-app-layout>
