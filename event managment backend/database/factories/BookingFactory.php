<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Show;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $seats = fake()->numberBetween(1, 6);
        $price = fake()->randomFloat(2, 199, 2499);

        return [
            'booking_reference' => strtoupper(fake()->bothify('BMS###??')),
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'show_id' => Show::factory(),
            'coupon_id' => null,
            'seats' => $seats,
            'subtotal' => $seats * $price,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $seats * $price,
            'status' => fake()->randomElement(['pending', 'confirmed']),
            'payment_status' => fake()->randomElement(['paid', 'pending', 'failed']),
            'payment_gateway' => 'manual',
            'payment_id' => strtoupper(fake()->bothify('PAY###??')),
            'refund_amount' => 0,
            'refund_status' => 'not_requested',
            'qr_token' => (string) fake()->uuid(),
            'ticket_path' => null,
            'booked_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'confirmed_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'cancelled_at' => null,
            'expires_at' => fake()->dateTimeBetween('now', '+1 day'),
            'notes' => null,
        ];
    }
}
