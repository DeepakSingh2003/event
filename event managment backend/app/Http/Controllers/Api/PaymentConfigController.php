<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;

class PaymentConfigController extends Controller
{
    public function __construct(private readonly SettingsService $settingsService)
    {
    }

    public function __invoke(): JsonResponse
    {
        $gateway = (string) $this->settingsService->get('payment.default_gateway', 'manual');
        $gateways = $this->settingsService->get('payment.enabled_gateways', [$gateway]);
        $gateways = is_array($gateways) ? $gateways : [$gateway];
        $gateways = array_values(array_intersect($gateways, ['manual', 'stripe', 'razorpay']));

        if ($gateways === []) {
            $gateways = ['manual'];
        }

        return response()->json([
            'data' => [
                'gateway' => in_array($gateway, ['manual', 'stripe', 'razorpay'], true) ? $gateway : 'manual',
                'gateways' => $gateways,
                'stripe_key' => $this->settingsService->get('payment.stripe_key'),
                'razorpay_key' => $this->settingsService->get('payment.razorpay_key'),
                'manual_qr_url' => $this->imageUrl($this->settingsService->get('payment.manual_qr_image')),
                'manual_instructions' => $this->settingsService->get('payment.manual_instructions', 'Scan the QR, pay the total amount, then upload your payment screenshot. Your ticket will be confirmed after admin verification.'),
                'currency' => strtoupper((string) $this->settingsService->get('localization.currency', 'INR')),
            ],
        ]);
    }

    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
