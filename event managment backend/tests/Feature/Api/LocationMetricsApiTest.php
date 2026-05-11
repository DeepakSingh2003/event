<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Event;
use App\Models\Show;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LocationMetricsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cities_api_returns_distinct_event_counts(): void
    {
        $india = Country::query()->create([
            'name' => 'India',
            'slug' => 'india',
            'iso_code' => 'IN',
            'is_active' => true,
        ]);

        $city = City::query()->create([
            'name' => 'Mumbai',
            'slug' => 'mumbai',
            'state' => 'Maharashtra',
            'country_id' => $india->id,
            'country' => 'India',
            'is_active' => true,
        ]);

        $venueOne = Venue::query()->create([
            'name' => 'Venue One',
            'city' => 'Mumbai',
            'city_id' => $city->id,
            'slug' => 'venue-one',
            'address' => 'Address 1',
            'total_seats' => 100,
            'row_count' => 10,
            'column_count' => 10,
        ]);

        $venueTwo = Venue::query()->create([
            'name' => 'Venue Two',
            'city' => 'Mumbai',
            'city_id' => $city->id,
            'slug' => 'venue-two',
            'address' => 'Address 2',
            'total_seats' => 150,
            'row_count' => 10,
            'column_count' => 15,
        ]);

        $category = Category::factory()->create();
        $eventOne = Event::factory()->create([
            'title' => 'Event One',
            'slug' => 'event-one',
            'category_id' => $category->id,
        ]);
        $eventTwo = Event::factory()->create([
            'title' => 'Event Two',
            'slug' => 'event-two',
            'category_id' => $category->id,
        ]);

        Show::factory()->create([
            'event_id' => $eventOne->id,
            'venue_id' => $venueOne->id,
        ]);
        Show::factory()->create([
            'event_id' => $eventOne->id,
            'venue_id' => $venueTwo->id,
        ]);
        Show::factory()->create([
            'event_id' => $eventTwo->id,
            'venue_id' => $venueTwo->id,
        ]);

        $response = $this->getJson('/api/cities');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Mumbai',
                'country_name' => 'India',
                'venues_count' => 2,
                'events_count' => 2,
            ]);
    }

    public function test_countries_api_returns_event_counts_and_excludes_empty_countries(): void
    {
        $india = Country::query()->create([
            'name' => 'India',
            'slug' => 'india',
            'iso_code' => 'IN',
            'is_active' => true,
        ]);

        $uae = Country::query()->create([
            'name' => 'United Arab Emirates',
            'slug' => 'united-arab-emirates',
            'iso_code' => 'AE',
            'is_active' => true,
        ]);

        $emptyCountry = Country::query()->create([
            'name' => 'Ghost Country',
            'slug' => 'ghost-country',
            'iso_code' => 'GC',
            'is_active' => true,
        ]);

        $mumbai = City::query()->create([
            'name' => 'Mumbai',
            'slug' => 'mumbai',
            'state' => 'Maharashtra',
            'country_id' => $india->id,
            'country' => 'India',
            'is_active' => true,
        ]);

        $dubai = City::query()->create([
            'name' => 'Dubai',
            'slug' => 'dubai',
            'state' => 'Dubai',
            'country_id' => $uae->id,
            'country' => 'United Arab Emirates',
            'is_active' => true,
        ]);

        $mumbaiVenue = Venue::query()->create([
            'name' => 'Mumbai Arena',
            'city' => 'Mumbai',
            'city_id' => $mumbai->id,
            'slug' => 'mumbai-arena',
            'address' => 'Mumbai Address',
            'total_seats' => 120,
            'row_count' => 10,
            'column_count' => 12,
        ]);

        $dubaiVenue = Venue::query()->create([
            'name' => 'Dubai Hall',
            'city' => 'Dubai',
            'city_id' => $dubai->id,
            'slug' => 'dubai-hall',
            'address' => 'Dubai Address',
            'total_seats' => 140,
            'row_count' => 10,
            'column_count' => 14,
        ]);

        $category = Category::factory()->create();
        $indiaEvent = Event::factory()->create([
            'title' => 'India Event',
            'slug' => 'india-event',
            'category_id' => $category->id,
        ]);
        $uaeEvent = Event::factory()->create([
            'title' => 'UAE Event',
            'slug' => 'uae-event',
            'category_id' => $category->id,
        ]);

        Show::factory()->create([
            'event_id' => $indiaEvent->id,
            'venue_id' => $mumbaiVenue->id,
        ]);
        Show::factory()->create([
            'event_id' => $uaeEvent->id,
            'venue_id' => $dubaiVenue->id,
        ]);

        $response = $this->getJson('/api/countries');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'India',
                'cities_count' => 1,
                'events_count' => 1,
            ])
            ->assertJsonFragment([
                'name' => 'United Arab Emirates',
                'cities_count' => 1,
                'events_count' => 1,
            ])
            ->assertJsonMissing([
                'name' => $emptyCountry->name,
            ]);
    }
}