<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $city = fake()->unique()->randomElement([
            ['name' => 'Mumbai', 'state' => 'Maharashtra'],
            ['name' => 'New Delhi', 'state' => 'Delhi'],
            ['name' => 'Bengaluru', 'state' => 'Karnataka'],
            ['name' => 'Hyderabad', 'state' => 'Telangana'],
            ['name' => 'Chennai', 'state' => 'Tamil Nadu'],
            ['name' => 'Kolkata', 'state' => 'West Bengal'],
            ['name' => 'Pune', 'state' => 'Maharashtra'],
            ['name' => 'Ahmedabad', 'state' => 'Gujarat'],
            ['name' => 'Jaipur', 'state' => 'Rajasthan'],
            ['name' => 'Lucknow', 'state' => 'Uttar Pradesh'],
            ['name' => 'Kochi', 'state' => 'Kerala'],
            ['name' => 'Chandigarh', 'state' => 'Chandigarh'],
        ]);

        return [
            'name' => $city['name'],
            'slug' => Str::slug($city['name']),
            'state' => $city['state'],
            'country' => 'India',
            'is_active' => true,
        ];
    }
}
