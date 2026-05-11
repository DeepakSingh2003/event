<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venue>
 */
class VenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'PVR Phoenix Grand',
            'INOX Megaplex',
            'Siri Fort Auditorium',
            'Bharat Mandapam Hall',
            'NCPA Tata Theatre',
            'Shanmukhananda Hall',
            'Talkatora Indoor Arena',
            'Kala Academy Auditorium',
            'Ravindra Bharathi Theatre',
            'Bal Gandharva Rang Mandir',
            'Nehru Centre Dome',
            'Jawaharlal Nehru Stadium Stand',
        ]);
        $rows = fake()->numberBetween(8, 14);
        $columns = fake()->numberBetween(8, 16);
        $address = sprintf(
            '%s, %s, %s',
            fake()->randomElement(['Gate 1', 'Plot 12', 'Tower A', 'Sector 5', 'Block C', 'Phase 2']),
            fake()->randomElement(['MG Road', 'Linking Road', 'Brigade Road', 'Banjara Hills', 'Anna Salai', 'Salt Lake', 'Sector 18', 'FC Road']),
            fake()->randomElement(['Business District', 'City Centre', 'Cultural Hub', 'West End', 'South Block'])
        );

        return [
            'name' => $name,
            'city' => fake()->randomElement(['Mumbai', 'New Delhi', 'Bengaluru', 'Hyderabad', 'Chennai', 'Pune']),
            'city_id' => fn () => City::query()->inRandomOrder()->value('id') ?? City::factory()->create()->id,
            'slug' => Str::slug($name),
            'address' => $address,
            'total_seats' => max($rows * $columns, fake()->numberBetween(120, 300)),
            'row_count' => $rows,
            'column_count' => $columns,
            'latitude' => fake()->randomFloat(6, 8.000000, 28.000000),
            'longitude' => fake()->randomFloat(6, 68.000000, 88.000000),
            'map_url' => 'https://maps.google.com/?q='.rawurlencode($name.' India'),
        ];
    }
}
