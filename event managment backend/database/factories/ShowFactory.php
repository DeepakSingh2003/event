<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Show>
 */
class ShowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $showDate = fake()->dateTimeBetween('-15 days', '+45 days');

        return [
            'event_id' => Event::factory(),
            'venue_id' => Venue::factory(),
            'show_date' => $showDate->format('Y-m-d'),
            'show_time' => fake()->randomElement(['10:30:00', '13:45:00', '17:30:00', '20:30:00']),
            'price' => fake()->randomElement([199, 249, 299, 349, 499, 699, 999, 1499]),
            'currency_code' => 'INR',
            'available_seats' => fake()->numberBetween(30, 250),
            'status' => 'scheduled',
            'booking_open_at' => now()->subDays(5),
            'booking_close_at' => now()->addDays(5),
            'seat_lock_minutes' => 10,
            'seat_map_generated_at' => null,
        ];
    }
}
