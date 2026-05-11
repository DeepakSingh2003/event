<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = fake()->unique()->randomElement([
            ['name' => 'Movies', 'description' => 'Bollywood, regional cinema, and premiere screenings.', 'icon' => 'Ticket'],
            ['name' => 'Concerts', 'description' => 'Live music tours, DJ nights, and unplugged sessions.', 'icon' => 'Music4'],
            ['name' => 'Stand-Up Comedy', 'description' => 'Comedy specials, open mics, and touring comics.', 'icon' => 'Mic2'],
            ['name' => 'Sports', 'description' => 'Live screenings, leagues, fan parks, and stadium events.', 'icon' => 'Trophy'],
            ['name' => 'Theatre', 'description' => 'Plays, dramas, and performing arts showcases.', 'icon' => 'Drama'],
            ['name' => 'Festivals', 'description' => 'Seasonal celebrations, cultural nights, and community events.', 'icon' => 'PartyPopper'],
            ['name' => 'Kids & Family', 'description' => 'Family-friendly entertainment and school holiday events.', 'icon' => 'ToyBrick'],
            ['name' => 'Business & Expo', 'description' => 'Summits, expos, networking meets, and conferences.', 'icon' => 'BriefcaseBusiness'],
        ]);

        return [
            'name' => $category['name'],
            'icon' => $category['icon'],
            'slug' => Str::slug($category['name']),
            'description' => $category['description'],
            'is_active' => true,
        ];
    }
}