<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentGatewayService
{
    public function __construct(private readonly SettingsService $settingsService)
    {
    }

    public function createCheckout(Booking $booking, string $gateway): array
    {
        return match ($gateway) {
            'stripe' => $this->createStripeIntent($booking),
            'razorpay' => $this->createRazorpayOrder($booking),
            default => $this->createManualReference($booking),
        };
    }

    private function createManualReference(Booking $booking): array
    {
        $reference = 'MANUAL-'.Str::upper(Str::random(10));

        $this->log($booking, 'manual', 'checkout_created', 'pending', $reference, [
            'booking_reference' => $booking->booking_reference,
        ], [
            'message' => 'Manual payment placeholder created.',
        ]);

        return [
            'gateway' => 'manual',
            'status' => 'pending',
            'payment_reference' => $reference,
            'message' => 'Manual payment flow initialized.',
        ];
    }

    private function createStripeIntent(Booking $booking): array
    {
        $secret = $this->settingsService->get('payment.stripe_secret');

        if (! $secret) {
            throw ValidationException::withMessages([
                'gateway' => 'Stripe secret key is not configured in settings.',
            ]);
        }

        $response = Http::asForm()
            ->withBasicAuth($secret, '')
            ->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => (int) round($booking->total_amount * 100),
                'currency' => strtolower((string) $this->settingsService->get('localization.currency', 'INR')),
                'metadata[booking_reference]' => $booking->booking_reference,
            ]);

        if ($response->failed()) {
            $this->log($booking, 'stripe', 'checkout_failed', 'failed', null, [], $response->json());

            throw ValidationException::withMessages([
                'gateway' => 'Stripe checkout creation failed.',
            ]);
        }

        $payload = $response->json();

        $this->log($booking, 'stripe', 'checkout_created', 'pending', $payload['id'] ?? null, [
            'booking_reference' => $booking->booking_reference,
        ], $payload);

        return [
            'gateway' => 'stripe',
            'status' => 'pending',
            'payment_reference' => $payload['id'] ?? null,
            'client_secret' => $payload['client_secret'] ?? null,
            'payload' => $payload,
        ];
    }

    private function createRazorpayOrder(Booking $booking): array
    {
        $key = $this->settingsService->get('payment.razorpay_key');
        $secret = $this->settingsService->get('payment.razorpay_secret');

        if (! $key || ! $secret) {
            throw ValidationException::withMessages([
                'gateway' => 'Razorpay credentials are not configured in settings.',
            ]);
        }

        $response = Http::withBasicAuth($key, $secret)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => (int) round($booking->total_amount * 100),
                'currency' => strtoupper((string) $this->settingsService->get('localization.currency', 'INR')),
                'receipt' => $booking->booking_reference,
            ]);

        if ($response->failed()) {
            $this->log($booking, 'razorpay', 'checkout_failed', 'failed', null, [], $response->json());

            throw ValidationException::withMessages([
                'gateway' => 'Razorpay order creation failed.',
            ]);
        }

        $payload = $response->json();

        $this->log($booking, 'razorpay', 'checkout_created', 'pending', $payload['id'] ?? null, [
            'booking_reference' => $booking->booking_reference,
        ], $payload);

        return [
            'gateway' => 'razorpay',
            'status' => 'pending',
            'payment_reference' => $payload['id'] ?? null,
            'payload' => $payload,
        ];
    }

    public function log(Booking $booking, string $gateway, string $action, string $status, ?string $reference, array $requestPayload, array $responsePayload): PaymentLog
    {
        return PaymentLog::create([
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'gateway' => $gateway,
            'action' => $action,
            'amount' => $booking->total_amount,
            'status' => $status,
            'payment_reference' => $reference,
            'request_payload' => $requestPayload,
            'response_payload' => $responsePayload,
            'logged_at' => now(),
        ]);
    }
}
