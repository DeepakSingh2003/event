<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\PaymentLog;
use App\Models\SeatType;
use App\Models\Show;
use App\Models\ShowSeat;
use App\Models\Tag;
use App\Models\User;
use App\Models\Venue;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $settings = app(SettingsService::class);
        $settings->putMany('general', ['site_name' => 'Event OS']);
        $settings->putMany('localization', ['currency' => 'INR', 'tax_percentage' => 18]);
        $settings->putMany('payment', ['default_gateway' => 'manual']);

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Aarav Sharma',
                'phone' => '9876543210',
                'role' => 'admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Priya Mehta',
                'phone' => '9865432109',
                'role' => 'manager',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        collect([
            ['name' => 'VIP', 'code' => 'VIP', 'description' => 'Premium lounge seats', 'color' => '#7c3aed', 'price_multiplier' => 2.2, 'is_active' => true],
            ['name' => 'Gold', 'code' => 'GOLD', 'description' => 'Front premium block', 'color' => '#ca8a04', 'price_multiplier' => 1.6, 'is_active' => true],
            ['name' => 'Silver', 'code' => 'SILVER', 'description' => 'Middle block', 'color' => '#64748b', 'price_multiplier' => 1.2, 'is_active' => true],
            ['name' => 'Normal', 'code' => 'NORMAL', 'description' => 'Standard seats', 'color' => '#2563eb', 'price_multiplier' => 1, 'is_active' => true],
        ])->each(fn (array $seatType) => SeatType::query()->updateOrCreate(['code' => $seatType['code']], $seatType));

        $categories = collect([
            ['name' => 'Movies', 'description' => 'Bollywood, regional cinema, and premiere screenings.', 'icon' => 'Ticket'],
            ['name' => 'Concerts', 'description' => 'Live music tours, DJ nights, and unplugged sessions.', 'icon' => 'Music4'],
            ['name' => 'Stand-Up Comedy', 'description' => 'Comedy specials, open mics, and touring comics.', 'icon' => 'Mic2'],
            ['name' => 'Sports', 'description' => 'Live screenings, leagues, fan parks, and stadium events.', 'icon' => 'Trophy'],
            ['name' => 'Theatre', 'description' => 'Plays, dramas, and performing arts showcases.', 'icon' => 'Drama'],
            ['name' => 'Festivals', 'description' => 'Seasonal celebrations, cultural nights, and community events.', 'icon' => 'PartyPopper'],
            ['name' => 'Kids & Family', 'description' => 'Family-friendly entertainment and school holiday events.', 'icon' => 'ToyBrick'],
            ['name' => 'Business & Expo', 'description' => 'Summits, expos, networking meets, and conferences.', 'icon' => 'BriefcaseBusiness'],
        ])->map(function (array $category) {
            return Category::query()->updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'slug' => Str::slug($category['name']),
                    'description' => $category['description'],
                    'is_active' => true,
                ]
            );
        });

        $countries = collect([
            ['name' => 'India', 'iso_code' => 'IN'],
            ['name' => 'United Arab Emirates', 'iso_code' => 'AE'],
            ['name' => 'Singapore', 'iso_code' => 'SG'],
            ['name' => 'United Kingdom', 'iso_code' => 'GB'],
            ['name' => 'United States', 'iso_code' => 'US'],
            ['name' => 'Belgium', 'iso_code' => 'BE'],
            ['name' => 'Brazil', 'iso_code' => 'BR'],
        ])->mapWithKeys(function (array $country) {
            $record = Country::query()->updateOrCreate(
                ['slug' => Str::slug($country['name'])],
                [
                    'name' => $country['name'],
                    'slug' => Str::slug($country['name']),
                    'iso_code' => $country['iso_code'],
                    'is_active' => true,
                ]
            );

            return [$country['name'] => $record];
        });

        $cities = collect([
            ['name' => 'Mumbai', 'state' => 'Maharashtra', 'country' => 'India'],
            ['name' => 'New Delhi', 'state' => 'Delhi', 'country' => 'India'],
            ['name' => 'Bengaluru', 'state' => 'Karnataka', 'country' => 'India'],
            ['name' => 'Hyderabad', 'state' => 'Telangana', 'country' => 'India'],
            ['name' => 'Chennai', 'state' => 'Tamil Nadu', 'country' => 'India'],
            ['name' => 'Kolkata', 'state' => 'West Bengal', 'country' => 'India'],
            ['name' => 'Pune', 'state' => 'Maharashtra', 'country' => 'India'],
            ['name' => 'Ahmedabad', 'state' => 'Gujarat', 'country' => 'India'],
            ['name' => 'Jaipur', 'state' => 'Rajasthan', 'country' => 'India'],
            ['name' => 'Lucknow', 'state' => 'Uttar Pradesh', 'country' => 'India'],
            ['name' => 'Dubai', 'state' => 'Dubai', 'country' => 'United Arab Emirates'],
            ['name' => 'Abu Dhabi', 'state' => 'Abu Dhabi', 'country' => 'United Arab Emirates'],
            ['name' => 'Singapore', 'state' => 'Central Region', 'country' => 'Singapore'],
            ['name' => 'London', 'state' => 'England', 'country' => 'United Kingdom'],
            ['name' => 'New York', 'state' => 'New York', 'country' => 'United States'],
            ['name' => 'Boom', 'state' => 'Antwerp', 'country' => 'Belgium'],
            ['name' => 'Indio', 'state' => 'California', 'country' => 'United States'],
            ['name' => 'Pilton', 'state' => 'England', 'country' => 'United Kingdom'],
            ['name' => 'Rio de Janeiro', 'state' => 'Rio de Janeiro', 'country' => 'Brazil'],
            ['name' => 'Miami', 'state' => 'Florida', 'country' => 'United States'],
            ['name' => 'Edinburgh', 'state' => 'Scotland', 'country' => 'United Kingdom'],
        ])->map(function (array $city) use ($countries) {
            return City::query()->updateOrCreate(
                ['slug' => Str::slug($city['name'])],
                [
                    'name' => $city['name'],
                    'slug' => Str::slug($city['name']),
                    'state' => $city['state'],
                    'country_id' => $countries[$city['country']]->id,
                    'country' => $city['country'],
                    'is_active' => true,
                ]
            );
        });

        $tags = collect([
            'Bollywood',
            'Weekend Plans',
            'Live Music',
            'Cricket Night',
            'Festive Season',
            'Premium Seats',
            'Family Outing',
            'Trending Now',
        ])->map(function (string $name) {
            return Tag::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        });

        User::factory(14)->create();

        $venues = Venue::factory(10)->make()->map(function (Venue $venue) use ($cities) {
            $existingVenue = Venue::query()->where('slug', $venue->slug)->first();

            if ($existingVenue) {
                return $existingVenue;
            }

            $city = $cities->random();
            $venue->city_id = $city->id;
            $venue->city = $city->name;
            $venue->save();

            return $venue;
        })->values();

        collect([
            [
                'title' => 'Backstage Siblings - Bhajan Jamming Night',
                'description' => 'A warm devotional jamming evening with live percussion, sing-along sets, and an intimate community vibe.',
                'category' => 'Concerts',
                'language' => 'Hindi',
                'city' => 'Mumbai',
                'venue' => 'Backstage Hall',
                'date' => now()->addDays(37)->toDateString(),
                'time' => '17:00:00',
                'price' => 1100,
                'image' => 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&q=80&w=600',
            ],
            [
                'title' => "Bollywood 90's Jamming - India Music Collective",
                'description' => 'A nostalgic Bollywood night celebrating iconic 90s hooks, acoustic medleys, and crowd-led chorus moments.',
                'category' => 'Concerts',
                'language' => 'Hindi',
                'city' => 'New Delhi',
                'venue' => 'Bharat Mandapam',
                'date' => now()->addDays(24)->toDateString(),
                'time' => '18:30:00',
                'price' => 499,
                'image' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&q=80&w=600',
            ],
            [
                'title' => 'SoundRise at the Pier: Season Closer',
                'description' => 'A sunset-to-night music showcase with indie artists, coastal ambience, and a high-energy finale set.',
                'category' => 'Concerts',
                'language' => 'English',
                'city' => 'Mumbai',
                'venue' => 'Radio Club, Colaba',
                'date' => now()->addDays(2)->toDateString(),
                'time' => '17:00:00',
                'price' => 849,
                'image' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=600',
            ],
            [
                'title' => 'SPACETECH Festival Pre Party - Delhi NCR',
                'description' => 'A late-night electronic pre-party with immersive lights, guest DJs, and premium lounge zones.',
                'category' => 'Festivals',
                'language' => 'English',
                'city' => 'New Delhi',
                'venue' => 'ROOM XO Gurgaon',
                'date' => now()->addDays(9)->toDateString(),
                'time' => '21:00:00',
                'price' => 1000,
                'image' => 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&q=80&w=600',
            ],
            [
                'title' => 'Techno Night: Under the Stars',
                'description' => 'An open-air techno experience with deep bass programming, visual rigs, and after-hours energy.',
                'category' => 'Concerts',
                'language' => 'English',
                'city' => 'Mumbai',
                'venue' => 'Antisocial, Mumbai',
                'date' => now()->addDays(15)->toDateString(),
                'time' => '22:00:00',
                'price' => 1200,
                'image' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?auto=format&fit=crop&q=80&w=600',
            ],
            [
                'title' => 'Acoustic Sunsets - Rooftop Session',
                'description' => 'A relaxed rooftop concert with acoustic artists, golden-hour seating, and curated food counters.',
                'category' => 'Concerts',
                'language' => 'English',
                'city' => 'Bengaluru',
                'venue' => 'The Local, Bengaluru',
                'date' => now()->addDays(17)->toDateString(),
                'time' => '18:00:00',
                'price' => 599,
                'image' => 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?auto=format&fit=crop&q=80&w=600',
            ],
            [
                'title' => 'Indie Rock Festival - Vol 4',
                'description' => 'A multi-band indie rock festival with main-stage performances, food stalls, and fan zones.',
                'category' => 'Festivals',
                'language' => 'English',
                'city' => 'New Delhi',
                'venue' => 'JL Nehru Stadium',
                'date' => now()->addDays(23)->toDateString(),
                'time' => '16:00:00',
                'price' => 1500,
                'image' => 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&q=80&w=600',
            ],
            [
                'title' => 'Comedy Roast Night: The Final Showdown',
                'description' => 'A sharp stand-up roast night featuring touring comics, surprise guests, and no-holds-barred punchlines.',
                'category' => 'Stand-Up Comedy',
                'language' => 'Hindi',
                'city' => 'Pune',
                'venue' => 'Comedy Store, Pune',
                'date' => now()->addDays(27)->toDateString(),
                'time' => '20:30:00',
                'price' => 350,
                'image' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?auto=format&fit=crop&q=80&w=600',
            ],
        ])->each(function (array $eventData) use ($categories, $cities, $tags) {
            $category = $categories->firstWhere('name', $eventData['category']) ?? $categories->first();
            $city = $cities->firstWhere('name', $eventData['city']) ?? $cities->first();
            $slug = Str::slug($eventData['title']);
            $showStartsAt = \Illuminate\Support\Carbon::parse($eventData['date'].' '.$eventData['time']);

            $venue = Venue::query()->updateOrCreate(
                ['slug' => Str::slug($eventData['venue'])],
                [
                    'name' => $eventData['venue'],
                    'city_id' => $city->id,
                    'city' => $city->name,
                    'address' => $eventData['venue'].', '.$city->name,
                    'total_seats' => 240,
                    'row_count' => 12,
                    'column_count' => 20,
                    'map_url' => 'https://maps.google.com/?q='.rawurlencode($eventData['venue'].' '.$city->name),
                ]
            );

            $event = Event::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $eventData['title'],
                    'description' => $eventData['description'],
                    'category' => $category->name,
                    'category_id' => $category->id,
                    'poster_image' => $eventData['image'],
                    'banner_image' => $eventData['image'],
                    'language' => $eventData['language'],
                    'status' => true,
                    'publication_status' => 'published',
                    'meta_title' => $eventData['title'],
                    'meta_description' => $eventData['description'],
                    'is_featured' => true,
                    'published_at' => now()->subHours(rand(2, 48)),
                ]
            );

            $event->tags()->syncWithoutDetaching($tags->whereIn('name', ['Weekend Plans', 'Live Music', 'Trending Now'])->pluck('id'));

            Show::query()->updateOrCreate(
                [
                    'event_id' => $event->id,
                    'venue_id' => $venue->id,
                    'show_date' => $eventData['date'],
                    'show_time' => $eventData['time'],
                ],
                [
                    'price' => $eventData['price'],
                    'currency_code' => 'INR',
                    'available_seats' => 180,
                    'status' => 'scheduled',
                    'booking_open_at' => now()->subDay(),
                    'booking_close_at' => $showStartsAt->copy()->subHours(2),
                    'seat_lock_minutes' => 10,
                ]
            );

            $event->timelines()->updateOrCreate(
                ['title' => 'Doors Open'],
                [
                    'description' => 'Entry starts one hour before the headline performance.',
                    'starts_at' => $showStartsAt->copy()->subHour(),
                    'ends_at' => $showStartsAt,
                    'sort_order' => 0,
                ]
            );
        });

        collect([
            [
                'title' => 'Tomorrowland 2026 - Belgium',
                'description' => 'A world-scale electronic music festival with massive stage production, global DJs, and full-day dance programming.',
                'category' => 'Festivals',
                'language' => 'English',
                'city' => 'Boom',
                'venue' => 'De Schorre Festival Park',
                'date' => now()->addMonths(3)->addDays(24)->toDateString(),
                'time' => '12:00:00',
                'price' => 120,
                'currency_code' => 'EUR',
                'image' => 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?auto=format&fit=crop&q=80&w=600',
            ],
            [
                'title' => 'Coachella 2026',
                'description' => 'A headline music and arts weekend featuring global performers, desert installations, and premium festival access.',
                'category' => 'Festivals',
                'language' => 'English',
                'city' => 'Indio',
                'venue' => 'Empire Polo Club',
                'date' => now()->addMonths(2)->addDays(10)->toDateString(),
                'time' => '13:00:00',
                'price' => 499,
                'currency_code' => 'USD',
                'image' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?auto=format&fit=crop&q=80&w=600',
            ],
            [
                'title' => 'Glastonbury Festival',
                'description' => 'A legendary UK festival with live music, theatre, comedy, and cultural programming across multiple stages.',
                'category' => 'Festivals',
                'language' => 'English',
                'city' => 'Pilton',
                'venue' => 'Worthy Farm',
                'date' => now()->addMonths(4)->addDays(24)->toDateString(),
                'time' => '12:00:00',
                'price' => 280,
                'currency_code' => 'GBP',
                'image' => 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?auto=format&fit=crop&q=80&w=600',
            ],
            [
                'title' => 'Rio Carnival',
                'description' => 'A vibrant carnival night with samba performances, costumes, parade energy, and premium viewing access.',
                'category' => 'Festivals',
                'language' => 'Portuguese',
                'city' => 'Rio de Janeiro',
                'venue' => 'Sambadrome Marquês de Sapucaí',
                'date' => now()->addMonths(9)->addDays(12)->toDateString(),
                'time' => '20:00:00',
                'price' => 300,
                'currency_code' => 'BRL',
                'image' => 'https://images.unsplash.com/photo-1545128485-c400e7702796?auto=format&fit=crop&q=80&w=600',
            ],
            [
                'title' => 'F1 Singapore Grand Prix',
                'description' => 'A night-race motorsport weekend with grandstand seating, fan zones, and Marina Bay skyline views.',
                'category' => 'Sports',
                'language' => 'English',
                'city' => 'Singapore',
                'venue' => 'Marina Bay Street Circuit',
                'date' => now()->addMonths(5)->addDays(20)->toDateString(),
                'time' => '20:00:00',
                'price' => 388,
                'currency_code' => 'SGD',
                'image' => 'https://images.unsplash.com/photo-1566737236500-c8ac43014a67?auto=format&fit=crop&q=80&w=600',
            ],
            [
                'title' => 'Ultra Music Festival Miami',
                'description' => 'A high-energy electronic music weekend with waterfront stages, international DJs, and festival lighting.',
                'category' => 'Festivals',
                'language' => 'English',
                'city' => 'Miami',
                'venue' => 'Bayfront Park',
                'date' => now()->addMonths(1)->addDays(27)->toDateString(),
                'time' => '14:00:00',
                'price' => 399,
                'currency_code' => 'USD',
                'image' => 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?auto=format&fit=crop&q=80&w=600',
            ],
            [
                'title' => 'Edinburgh Fringe Festival',
                'description' => 'A city-wide performing arts festival with comedy, theatre, spoken word, music, and experimental shows.',
                'category' => 'Theatre',
                'language' => 'English',
                'city' => 'Edinburgh',
                'venue' => 'Edinburgh Festival Theatre',
                'date' => now()->addMonths(6)->addDays(3)->toDateString(),
                'time' => '10:00:00',
                'price' => 25,
                'currency_code' => 'GBP',
                'image' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&q=80&w=600',
            ],
        ])->each(function (array $eventData) use ($categories, $cities, $tags) {
            $category = $categories->firstWhere('name', $eventData['category']) ?? $categories->first();
            $city = $cities->firstWhere('name', $eventData['city']) ?? $cities->first();
            $slug = Str::slug($eventData['title']);
            $showStartsAt = \Illuminate\Support\Carbon::parse($eventData['date'].' '.$eventData['time']);

            $venue = Venue::query()->updateOrCreate(
                ['slug' => Str::slug($eventData['venue'])],
                [
                    'name' => $eventData['venue'],
                    'city_id' => $city->id,
                    'city' => $city->name,
                    'address' => $eventData['venue'].', '.$city->name,
                    'total_seats' => 500,
                    'row_count' => 20,
                    'column_count' => 25,
                    'map_url' => 'https://maps.google.com/?q='.rawurlencode($eventData['venue'].' '.$city->name),
                ]
            );

            $event = Event::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $eventData['title'],
                    'description' => $eventData['description'],
                    'category' => $category->name,
                    'category_id' => $category->id,
                    'poster_image' => $eventData['image'],
                    'banner_image' => $eventData['image'],
                    'language' => $eventData['language'],
                    'status' => true,
                    'publication_status' => 'published',
                    'meta_title' => $eventData['title'],
                    'meta_description' => $eventData['description'],
                    'is_featured' => true,
                    'published_at' => now()->subHours(rand(2, 48)),
                ]
            );

            $event->tags()->syncWithoutDetaching($tags->whereIn('name', ['Weekend Plans', 'Festive Season', 'Trending Now'])->pluck('id'));

            Show::query()->updateOrCreate(
                [
                    'event_id' => $event->id,
                    'venue_id' => $venue->id,
                    'show_date' => $eventData['date'],
                    'show_time' => $eventData['time'],
                ],
                [
                    'price' => $eventData['price'],
                    'currency_code' => $eventData['currency_code'],
                    'available_seats' => 420,
                    'status' => 'scheduled',
                    'booking_open_at' => now()->subDay(),
                    'booking_close_at' => $showStartsAt->copy()->subHours(2),
                    'seat_lock_minutes' => 10,
                ]
            );

            $event->timelines()->updateOrCreate(
                ['title' => 'Gates Open'],
                [
                    'description' => 'Entry begins before the main programming starts.',
                    'starts_at' => $showStartsAt->copy()->subHour(),
                    'ends_at' => $showStartsAt,
                    'sort_order' => 0,
                ]
            );
        });

        collect([
            [
                'code' => 'WELCOME10',
                'description' => 'Welcome discount for first-time customers',
                'type' => 'percentage',
                'value' => 10,
                'min_amount' => 500,
                'usage_limit' => 100,
                'used_count' => 0,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonth(),
                'is_active' => true,
            ],
            [
                'code' => 'FESTIVE15',
                'description' => 'Festival season savings on premium tickets',
                'type' => 'percentage',
                'value' => 15,
                'min_amount' => 999,
                'usage_limit' => 150,
                'used_count' => 0,
                'starts_at' => now()->subDays(3),
                'expires_at' => now()->addMonths(2),
                'is_active' => true,
            ],
        ])->each(function (array $coupon) {
            Coupon::query()->updateOrCreate(['code' => $coupon['code']], $coupon);
        });

        $events = Event::factory(10)->make()->map(function (Event $event) use ($categories, $tags) {
            $existingEvent = Event::query()->where('slug', $event->slug)->first();

            if ($existingEvent) {
                return $existingEvent;
            }

            $category = $categories->random();
            $event->category_id = $category->id;
            $event->category = $category->name;
            $event->publication_status = 'published';
            $event->status = true;
            $event->is_featured = fake()->boolean(45);
            $event->published_at = now()->subDays(rand(1, 20));
            $event->save();

            $event->tags()->sync($tags->random(rand(2, 4))->pluck('id'));

            $event->timelines()->createMany([
                [
                    'title' => 'Teaser Campaign',
                    'description' => 'Promotions go live across social media, city listings, and partner channels.',
                    'starts_at' => now()->subDays(rand(2, 12)),
                    'ends_at' => now()->subDays(rand(0, 4)),
                    'sort_order' => 0,
                ],
                [
                    'title' => 'Prime Booking Window',
                    'description' => 'Peak sales period for weekend audiences and premium seat upgrades.',
                    'starts_at' => now()->addDays(rand(2, 9)),
                    'ends_at' => now()->addDays(rand(10, 24)),
                    'sort_order' => 1,
                ],
            ]);
            
            return $event;
        })->values();

        $events->each(function (Event $event) use ($venues): void {
            $shows = Show::factory(rand(2, 5))
                ->for($event)
                ->state(fn () => [
                    'venue_id' => $venues->random()->id,
                ])
                ->create();

            $shows->each(function (Show $show) use ($event): void {
                $seatTypes = SeatType::query()->get()->keyBy('code');
                $rows = max(1, (int) $show->venue->row_count);
                $columns = max(1, (int) $show->venue->column_count);
                $showSeats = [];

                for ($rowIndex = 0; $rowIndex < $rows; $rowIndex++) {
                    $rowLabel = chr(65 + ($rowIndex % 26));
                    $seatType = match (true) {
                        ($rowIndex + 1) / $rows <= 0.2 => $seatTypes['VIP'],
                        ($rowIndex + 1) / $rows <= 0.45 => $seatTypes['GOLD'],
                        ($rowIndex + 1) / $rows <= 0.75 => $seatTypes['SILVER'],
                        default => $seatTypes['NORMAL'],
                    };

                    for ($column = 1; $column <= $columns; $column++) {
                        $showSeats[] = [
                            'show_id' => $show->id,
                            'seat_type_id' => $seatType->id,
                            'row_label' => $rowLabel,
                            'column_number' => $column,
                            'seat_number' => $rowLabel.$column,
                            'base_price' => $show->price,
                            'price' => round($show->price * $seatType->price_multiplier, 2),
                            'status' => 'available',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                ShowSeat::query()->insert($showSeats);
                $show->update([
                    'available_seats' => count($showSeats),
                    'seat_map_generated_at' => now(),
                ]);

                $bookingsCount = rand(4, 10);

                for ($index = 0; $index < $bookingsCount; $index++) {
                    $selectedSeats = ShowSeat::query()
                        ->where('show_id', $show->id)
                        ->where('status', 'available')
                        ->inRandomOrder()
                        ->take(rand(1, 4))
                        ->get();

                    if ($selectedSeats->isEmpty()) {
                        continue;
                    }

                    $subtotal = $selectedSeats->sum('price');
                    $discount = rand(0, 1) ? round($subtotal * 0.1, 2) : 0;
                    $tax = round(($subtotal - $discount) * 0.18, 2);
                    $total = $subtotal - $discount + $tax;
                    $user = User::query()->where('role', 'user')->inRandomOrder()->first();

                    if (! $user) {
                        continue;
                    }

                    $paymentStatus = fake()->randomElement(['paid', 'paid', 'paid', 'failed']);
                    $paymentGateway = fake()->randomElement(['manual', 'stripe', 'razorpay']);

                    $booking = Booking::create([
                        'booking_reference' => strtoupper(fake()->unique()->bothify('EVT###??')),
                        'user_id' => $user->id,
                        'event_id' => $event->id,
                        'show_id' => $show->id,
                        'seats' => $selectedSeats->count(),
                        'subtotal' => $subtotal,
                        'discount_amount' => $discount,
                        'tax_amount' => $tax,
                        'total_amount' => $total,
                        'status' => 'confirmed',
                        'payment_status' => $paymentStatus,
                        'payment_gateway' => $paymentGateway,
                        'payment_id' => strtoupper(fake()->bothify('PAY###??')),
                        'refund_amount' => 0,
                        'refund_status' => 'not_requested',
                        'qr_token' => (string) Str::uuid(),
                        'booked_at' => fake()->dateTimeBetween('-60 days', 'now'),
                        'confirmed_at' => now(),
                    ]);

                    foreach ($selectedSeats as $seat) {
                        $seat->update([
                            'status' => 'booked',
                            'booking_id' => $booking->id,
                            'booked_at' => now(),
                        ]);

                        BookingItem::create([
                            'booking_id' => $booking->id,
                            'show_seat_id' => $seat->id,
                            'seat_number' => $seat->seat_number,
                            'seat_type_name' => $seat->seatType?->name,
                            'unit_price' => $seat->price,
                            'status' => 'confirmed',
                        ]);
                    }

                    PaymentLog::create([
                        'user_id' => $user->id,
                        'booking_id' => $booking->id,
                        'gateway' => $paymentGateway,
                        'action' => 'checkout_created',
                        'amount' => $booking->total_amount,
                        'status' => $paymentStatus === 'failed' ? 'failed' : 'success',
                        'payment_reference' => $booking->payment_id,
                        'request_payload' => ['booking_reference' => $booking->booking_reference],
                        'response_payload' => ['message' => 'Seeded payment log'],
                        'logged_at' => now(),
                    ]);
                }

                $show->update([
                    'available_seats' => ShowSeat::query()
                        ->where('show_id', $show->id)
                        ->where('status', 'available')
                        ->count(),
                ]);
            });
        });
    }
}
