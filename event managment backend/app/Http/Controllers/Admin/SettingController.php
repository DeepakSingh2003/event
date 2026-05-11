<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function index(): View
    {
        $settings = $this->settingsService->grouped();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_logo' => ['nullable', 'image', 'max:2048'],
            'currency' => ['required', 'string', 'max:10'],
            'tax_percentage' => ['required', 'numeric', 'min:0'],
            'payment_gateway' => ['required', 'in:manual,stripe,razorpay'],
            'payment_gateways' => ['nullable', 'array'],
            'payment_gateways.*' => ['in:manual,stripe,razorpay'],
            'stripe_key' => ['nullable', 'string'],
            'stripe_secret' => ['nullable', 'string'],
            'razorpay_key' => ['nullable', 'string'],
            'razorpay_secret' => ['nullable', 'string'],
            'manual_qr_image' => ['nullable', 'image', 'max:4096'],
            'manual_instructions' => ['nullable', 'string', 'max:1000'],
            'remove_manual_qr_image' => ['nullable', 'boolean'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['nullable', 'email'],
        ]);

        $general = [
            'site_name' => $data['site_name'],
        ];

        if ($request->hasFile('site_logo')) {
            $general['site_logo'] = $request->file('site_logo')->store('settings', 'public');
        }

        $this->settingsService->putMany('general', $general);
        $this->settingsService->putMany('localization', [
            'currency' => strtoupper($data['currency']),
            'tax_percentage' => $data['tax_percentage'],
        ]);
        $manualQrImage = $this->settingsService->get('payment.manual_qr_image', '');

        if ($request->hasFile('manual_qr_image')) {
            if ($manualQrImage) {
                Storage::disk('public')->delete($manualQrImage);
            }

            $manualQrImage = $request->file('manual_qr_image')->store('payment-qr', 'public');
        }

        if ($request->boolean('remove_manual_qr_image') && $manualQrImage) {
            Storage::disk('public')->delete($manualQrImage);
            $manualQrImage = '';
        }

        $enabledGateways = $data['payment_gateways'] ?? [$data['payment_gateway']];

        if (! in_array($data['payment_gateway'], $enabledGateways, true)) {
            $enabledGateways[] = $data['payment_gateway'];
        }

        $this->settingsService->putMany('payment', [
            'default_gateway' => $data['payment_gateway'],
            'enabled_gateways' => array_values(array_unique($enabledGateways)),
            'stripe_key' => $data['stripe_key'] ?? '',
            'stripe_secret' => $data['stripe_secret'] ?? '',
            'razorpay_key' => $data['razorpay_key'] ?? '',
            'razorpay_secret' => $data['razorpay_secret'] ?? '',
            'manual_qr_image' => $manualQrImage,
            'manual_instructions' => $data['manual_instructions'] ?? '',
        ]);
        $this->settingsService->putMany('email', [
            'from_name' => $data['mail_from_name'] ?? '',
            'from_address' => $data['mail_from_address'] ?? '',
        ]);

        $this->activityLogService->log($request->user(), 'settings.updated', null, 'System settings updated.');

        return back()->with('success', 'Settings updated successfully.');
    }
}
