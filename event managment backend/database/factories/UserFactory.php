<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->randomElement([
            'Aarav', 'Vivaan', 'Aditya', 'Ishaan', 'Kabir', 'Rohan', 'Rahul', 'Arjun',
            'Ananya', 'Diya', 'Priya', 'Sneha', 'Kavya', 'Meera', 'Ira', 'Aditi',
        ]);
        $lastName = fake()->randomElement([
            'Sharma', 'Verma', 'Patel', 'Reddy', 'Iyer', 'Mehta', 'Kapoor', 'Khanna',
            'Gupta', 'Nair', 'Menon', 'Bose', 'Joshi', 'Singh', 'Chopra', 'Kulkarni',
        ]);
        $name = $firstName.' '.$lastName;
        $phonePrefix = fake()->randomElement(['98', '97', '96', '95', '93', '92', '91', '88', '87', '86', '83', '82']);

        return [
            'name' => $name,
            'email' => Str::slug($firstName.'.'.$lastName).fake()->unique()->numberBetween(10, 99).'@example.com',
            'phone' => $phonePrefix.fake()->numerify('########'),
            'email_verified_at' => now(),
            'role' => 'user',
            'is_blocked' => false,
            'blocked_at' => null,
            'last_active_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return $this
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
