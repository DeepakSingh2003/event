<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-rose-500">Settings</p>
            <h2 class="text-3xl font-semibold text-slate-900">System Settings</h2>
        </div>
    </x-slot>

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="panel-card p-6">
            <h3 class="text-lg font-semibold">Website Settings</h3>
            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div>
                    <label class="panel-label">Site Name</label>
                    <input type="text" name="site_name" class="panel-input" value="{{ data_get($settings, 'general.site_name') }}">
                </div>
                <div>
                    <label class="panel-label">Site Logo</label>
                    <input type="file" name="site_logo" class="panel-input">
                </div>
            </div>
        </div>

        <div class="panel-card p-6">
            <h3 class="text-lg font-semibold">Payment Settings</h3>
            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div>
                    <label class="panel-label">Default Gateway</label>
                    <select name="payment_gateway" class="panel-select">
                        @foreach (['manual', 'stripe', 'razorpay'] as $gateway)
                            <option value="{{ $gateway }}" @selected(data_get($settings, 'payment.default_gateway', 'manual') === $gateway)>{{ ucfirst($gateway) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="panel-label">Enabled Checkout Options</label>
                    @php
                        $enabledGateways = data_get($settings, 'payment.enabled_gateways', [data_get($settings, 'payment.default_gateway', 'manual')]);
                        $enabledGateways = is_array($enabledGateways) ? $enabledGateways : [$enabledGateways];
                    @endphp
                    <div class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @foreach (['manual' => 'QR / Upload Screenshot', 'razorpay' => 'Razorpay', 'stripe' => 'Stripe Card'] as $gateway => $label)
                            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                <input type="checkbox" name="payment_gateways[]" value="{{ $gateway }}" class="rounded border-slate-300 text-rose-500" @checked(in_array($gateway, $enabledGateways, true))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="panel-label">Currency</label>
                    <input type="text" name="currency" class="panel-input" value="{{ data_get($settings, 'localization.currency', 'INR') }}">
                </div>
                <div>
                    <label class="panel-label">Stripe Key</label>
                    <input type="text" name="stripe_key" class="panel-input" value="{{ data_get($settings, 'payment.stripe_key') }}">
                </div>
                <div>
                    <label class="panel-label">Stripe Secret</label>
                    <input type="text" name="stripe_secret" class="panel-input" value="{{ data_get($settings, 'payment.stripe_secret') }}">
                </div>
                <div>
                    <label class="panel-label">Razorpay Key</label>
                    <input type="text" name="razorpay_key" class="panel-input" value="{{ data_get($settings, 'payment.razorpay_key') }}">
                </div>
                <div>
                    <label class="panel-label">Razorpay Secret</label>
                    <input type="text" name="razorpay_secret" class="panel-input" value="{{ data_get($settings, 'payment.razorpay_secret') }}">
                </div>
                <div>
                    <label class="panel-label">Manual Payment QR</label>
                    <input type="file" name="manual_qr_image" accept="image/*" class="panel-input">
                    @if (data_get($settings, 'payment.manual_qr_image'))
                        <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                            <img src="{{ asset('storage/'.data_get($settings, 'payment.manual_qr_image')) }}" alt="Manual payment QR" class="max-h-56 w-full object-contain">
                        </div>
                        <label class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="remove_manual_qr_image" value="1">
                            Remove current QR
                        </label>
                    @endif
                </div>
                <div>
                    <label class="panel-label">Manual Payment Instructions</label>
                    <textarea name="manual_instructions" rows="5" class="panel-input" placeholder="Scan QR and upload payment screenshot">{{ data_get($settings, 'payment.manual_instructions') }}</textarea>
                </div>
            </div>
        </div>

        <div class="panel-card p-6">
            <h3 class="text-lg font-semibold">Email & Tax</h3>
            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div>
                    <label class="panel-label">Mail From Name</label>
                    <input type="text" name="mail_from_name" class="panel-input" value="{{ data_get($settings, 'email.from_name') }}">
                </div>
                <div>
                    <label class="panel-label">Mail From Address</label>
                    <input type="email" name="mail_from_address" class="panel-input" value="{{ data_get($settings, 'email.from_address') }}">
                </div>
                <div>
                    <label class="panel-label">Tax Percentage</label>
                    <input type="number" step="0.01" name="tax_percentage" class="panel-input" value="{{ data_get($settings, 'localization.tax_percentage', 0) }}">
                </div>
            </div>
        </div>

        <button class="panel-btn" type="submit">Save Settings</button>
    </form>
</x-app-layout>
