<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $event = fake()->unique()->randomElement([
            [
                'title' => 'Arijit Singh Live in Mumbai',
                'description' => 'A premium live concert featuring Bollywood chartbusters, acoustic favourites, and a high-energy arena setup.',
                'language' => 'Hindi',
                'category' => 'Concerts',
            ],
            [
                'title' => 'Zakir Khan: Papa Yaar Encore',
                'description' => 'A sold-out style stand-up experience packed with relatable storytelling, crowd work, and a polished theatre setup.',
                'language' => 'Hindi',
                'category' => 'Stand-Up Comedy',
            ],
            [
                'title' => 'Chennai Retro Beats Night',
                'description' => 'A musical tribute to Tamil retro hits with a full live band, immersive lights, and family-friendly seating.',
                'language' => 'Tamil',
                'category' => 'Concerts',
            ],
            [
                'title' => 'IPL Fan Park Screening',
                'description' => 'An outdoor cricket screening experience with commentary, food courts, fan zones, and merchandise counters.',
                'language' => 'Hindi',
                'category' => 'Sports',
            ],
            [
                'title' => 'Delhi Theatre Festival Opening Weekend',
                'description' => 'A curated stage lineup with contemporary drama, ensemble performances, and premium front-row experiences.',
                'language' => 'Hindi',
                'category' => 'Theatre',
            ],
            [
                'title' => 'Hyderabad Stand-Up Weekend',
                'description' => 'A back-to-back comedy showcase featuring Telugu and bilingual comics across multiple evening slots.',
                'language' => 'Telugu',
                'category' => 'Stand-Up Comedy',
            ],
            [
                'title' => 'Sufi Sandhya at the Fort',
                'description' => 'A soulful evening of qawwali and Sufi performances designed for premium cultural programming.',
                'language' => 'Hindi',
                'category' => 'Concerts',
            ],
            [
                'title' => 'Bengaluru Indie Music Showcase',
                'description' => 'A curated indie music lineup with rising bands, acoustic acts, and a modern standing-plus-seated layout.',
                'language' => 'English',
                'category' => 'Concerts',
            ],
            [
                'title' => 'Garba Mahotsav 2026',
                'description' => 'A festive Navratri celebration with live folk music, dance-friendly floor plans, and family seating zones.',
                'language' => 'Gujarati',
                'category' => 'Festivals',
            ],
            [
                'title' => 'Malayalam Superhits Musical Night',
                'description' => 'A nostalgia-driven music event focused on evergreen Malayalam melodies and regional audience engagement.',
                'language' => 'Malayalam',
                'category' => 'Concerts',
            ],
            [
                'title' => 'Pune Comedy Carnival',
                'description' => 'A city-scale comedy night bringing together touring headliners, local acts, and premium lounge seating.',
                'language' => 'Hindi',
                'category' => 'Stand-Up Comedy',
            ],
            [
                'title' => 'Kolkata Rabindra Sangeet Evening',
                'description' => 'A graceful concert experience celebrating Rabindra Sangeet in a theatre-first premium venue format.',
                'language' => 'Bengali',
                'category' => 'Concerts',
            ],
            [
                'title' => 'Kannada Blockbuster Premiere Night',
                'description' => 'An opening weekend movie premiere with celebrity appearances, media walls, and premium reserved seating.',
                'language' => 'Kannada',
                'category' => 'Movies',
            ],
            [
                'title' => 'Startup Bharat Leadership Summit',
                'description' => 'A high-ticket business summit for founders, operators, and investors with conference-style venue seating.',
                'language' => 'English',
                'category' => 'Business & Expo',
            ],
            [
                'title' => 'Mumbai Kids Summer Carnival',
                'description' => 'A weekend family event with stage entertainment, activity zones, and easy group booking experiences.',
                'language' => 'Hindi',
                'category' => 'Kids & Family',
            ],
        ]);

        return [
            'title' => $event['title'],
            'description' => $event['description'],
            'category' => $event['category'],
            'category_id' => fn () => Category::query()->inRandomOrder()->value('id') ?? Category::factory()->create()->id,
            'slug' => Str::slug($event['title']),
            'poster_image' => null,
            'banner_image' => null,
            'language' => $event['language'],
            'status' => fake()->boolean(85),
            'publication_status' => fake()->randomElement(['draft', 'published']),
            'meta_title' => $event['title'],
            'meta_description' => $event['description'],
            'is_featured' => fake()->boolean(30),
            'published_at' => now(),
        ];
    }
}
