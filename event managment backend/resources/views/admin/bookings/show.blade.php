<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Booking Details</p>
            <h2 class="text-3xl font-semibold text-slate-900">{{ $booking->booking_reference }}</h2>
        </div>
        <div class="flex gap-3">
            @if ($booking->ticket_path)
                <a href="{{ route('admin.bookings.ticket', $booking) }}" class="panel-btn-secondary">Download Ticket</a>
            @endif
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="panel-card p-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-slate-500">User</p>
                        <p class="mt-2 text-lg font-semibold">{{ $booking->user->name }}</p>
                        <p class="text-sm text-slate-500">{{ $booking->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Event</p>
                        <p class="mt-2 text-lg font-semibold">{{ $booking->event->title }}</p>
                        <p class="text-sm text-slate-500">{{ $booking->show->venue->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Booking Status</p>
                        <p class="mt-2 text-lg font-semibold">{{ ucfirst($booking->status) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Payment</p>
                        <p class="mt-2 text-lg font-semibold">{{ ucfirst($booking->payment_status) }}</p>
                        <p class="text-sm text-slate-500">{{ $booking->payment_gateway }} | {{ $booking->payment_id }}</p>
                    </div>
                </div>
            </div>

            @if ($booking->payment_gateway === 'manual')
                <div class="panel-card p-6">
                    <h3 class="text-lg font-semibold">Manual Payment Proof</h3>
                    @if ($booking->payment_proof_path)
                        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                            <img src="{{ asset('storage/'.$booking->payment_proof_path) }}" alt="Payment proof" class="max-h-[520px] w-full object-contain">
                        </div>
                        <a href="{{ asset('storage/'.$booking->payment_proof_path) }}" target="_blank" class="panel-btn-secondary mt-4 inline-flex">Open Proof</a>
                    @else
                        <p class="mt-3 rounded-2xl bg-amber-50 p-4 text-sm text-amber-700">No payment screenshot uploaded yet.</p>
                    @endif
                </div>
            @endif

            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Booked Seats</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="panel-table">
                        <thead class="text-left text-slate-500">
                            <tr>
                                <th class="pb-3">Seat</th>
                                <th class="pb-3">Type</th>
                                <th class="pb-3">Price</th>
                                <th class="pb-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($booking->items as $item)
                                <tr>
                                    <td class="py-3 font-semibold">{{ $item->seat_number }}</td>
                                    <td class="py-3">{{ $item->seat_type_name }}</td>
                                    <td class="py-3">{{ \App\Support\Currency::inr($item->unit_price) }}</td>
                                    <td class="py-3">{{ $item->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Payment Logs</h3>
                <div class="mt-4 space-y-3">
                    @foreach ($booking->paymentLogs as $log)
                        <div class="rounded-2xl border border-slate-100 px-4 py-3">
                            <p class="font-semibold">{{ $log->gateway }} - {{ $log->action }}</p>
                            <p class="text-sm text-slate-500">{{ $log->status }} | {{ optional($log->logged_at)->format('d M Y h:i A') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Financials</h3>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <p><span class="font-semibold text-slate-900">Subtotal:</span> {{ \App\Support\Currency::inr($booking->subtotal) }}</p>
                    <p><span class="font-semibold text-slate-900">Discount:</span> {{ \App\Support\Currency::inr($booking->discount_amount) }}</p>
                    <p><span class="font-semibold text-slate-900">Tax:</span> {{ \App\Support\Currency::inr($booking->tax_amount) }}</p>
                    <p><span class="font-semibold text-slate-900">Total:</span> {{ \App\Support\Currency::inr($booking->total_amount) }}</p>
                    <p><span class="font-semibold text-slate-900">Refund:</span> {{ \App\Support\Currency::inr($booking->refund_amount) }} ({{ $booking->refund_status }})</p>
                </div>
            </div>

            <div class="panel-card p-6">
                <h3 class="text-lg font-semibold">Actions</h3>
                <div class="mt-4 space-y-4">
                    @if ($booking->status !== 'confirmed')
                        <form action="{{ route('admin.bookings.confirm', $booking) }}" method="POST" class="space-y-3">
                            @csrf
                            <input type="text" name="payment_id" class="panel-input" placeholder="Payment reference" value="{{ $booking->payment_id }}">
                            @if ($booking->payment_gateway === 'manual' && $booking->payment_proof_path)
                                <p class="rounded-2xl bg-emerald-50 p-3 text-xs font-semibold text-emerald-700">Verify the uploaded payment screenshot before confirming. Ticket will be generated after confirm.</p>
                            @endif
                            <button class="panel-btn w-full" type="submit">Confirm Booking</button>
                        </form>
                    @endif

                    @if ($booking->status !== 'cancelled')
                        <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" class="space-y-3">
                            @csrf
                            <textarea name="reason" rows="2" class="panel-input" placeholder="Cancellation reason"></textarea>
                            <button class="panel-btn-secondary w-full" type="submit">Cancel Booking</button>
                        </form>
                    @endif

                    <form action="{{ route('admin.bookings.refund', $booking) }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="number" step="0.01" name="amount" class="panel-input" placeholder="Refund amount">
                        <textarea name="reason" rows="2" class="panel-input" placeholder="Refund reason"></textarea>
                        <button class="panel-btn-secondary w-full" type="submit">Process Refund</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
