<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Event;
use App\Models\SeatType;
use App\Models\Show;
use App\Models\ShowSeat;
use App\Models\User;
use App\Models\Venue;
use App\Services\SeatLayoutService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BookingModeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $india = Country::query()->firstOrCreate(
            ['slug' => 'india'],
            ['name' => 'India', 'iso_code' => 'IN', 'is_active' => true]
        );

        $city = City::query()->updateOrCreate(
            ['slug' => 'bengaluru'],
            [
                'name' => 'Bengaluru',
                'state' => 'Karnataka',
                'country_id' => $india->id,
                'country' => 'India',
                'is_active' => true,
            ]
        );

        $category = Category::query()->updateOrCreate(
            ['slug' => 'concerts'],
            [
                'name' => 'Concerts',
                'icon' => 'Music4',
                'description' => 'Live music tours, DJ nights, and unplugged sessions.',
                'is_active' => true,
            ]
        );

        $user = User::query()->firstOrCreate(
            ['email' => 'demo.user@example.com'],
            [
                'name' => 'Demo User',
                'phone' => '9000000000',
                'role' => 'user',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $venue = Venue::query()->updateOrCreate(
            ['slug' => 'demo-bengaluru-arena'],
            [
                'name' => 'Demo Bengaluru Arena',
                'city_id' => $city->id,
                'city' => $city->name,
                'address' => 'MG Road, Bengaluru',
                'total_seats' => 60,
                'row_count' => 6,
                'column_count' => 10,
                'map_url' => 'https://maps.google.com/?q=MG+Road+Bengaluru',
            ]
        );

        $this->ensureSeatTypes();

        $this->createReservedSeatingDemo($category, $venue, $user);
        $this->createGeneralAdmissionDemo($category, $venue);
        $this->createTieredTicketDemo($category, $venue);
    }

    private function createReservedSeatingDemo(Category $category, Venue $venue, User $user): void
    {
        $event = $this->event(
            'Demo Reserved Seat Concert',
            'Reserved seating demo with selectable seats, booked seats, and admin blocked seats.',
            $category,
            'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=900'
        );

        $show = Show::query()->updateOrCreate(
            [
                'event_id' => $event->id,
                'venue_id' => $venue->id,
                'show_date' => now()->addDays(10)->toDateString(),
                'show_time' => '19:30:00',
            ],
            [
                'price' => 500,
                'currency_code' => 'INR',
                'available_seats' => 60,
                'sales_capacity' => null,
                'booking_mode' => 'reserved_seating',
                'status' => 'scheduled',
                'booking_open_at' => now()->subDay(),
                'booking_close_at' => now()->addDays(10)->setTime(17, 30),
                'seat_lock_minutes' => 10,
            ]
        );

        app(SeatLayoutService::class)->generateForShow($show->load('venue'), true);

        $show->seats()->whereIn('seat_number', ['A1', 'A2', 'B5', 'C7'])->update([
            'status' => ShowSeat::STATUS_BLOCKED,
            'updated_at' => now(),
        ]);

        $bookedSeats = $show->seats()
            ->whereIn('seat_number', ['A3', 'A4', 'D1'])
            ->get();

        if ($bookedSeats->isNotEmpty() && ! Booking::query()->where('booking_reference', 'DEMO-SEATED-001')->exists()) {
            $booking = Booking::create([
                'booking_reference' => 'DEMO-SEATED-001',
                'user_id' => $user->id,
                'event_id' => $event->id,
                'show_id' => $show->id,
                'seats' => $bookedSeats->count(),
                'subtotal' => $bookedSeats->sum('price'),
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $bookedSeats->sum('price'),
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_gateway' => 'manual',
                'payment_id' => 'DEMO-PAY-SEATED',
                'refund_amount' => 0,
                'refund_status' => 'not_requested',
                'qr_token' => (string) Str::uuid(),
                'booked_at' => now(),
                'confirmed_at' => now(),
            ]);

            foreach ($bookedSeats as $seat) {
                $seat->update([
                    'status' => ShowSeat::STATUS_BOOKED,
                    'booking_id' => $booking->id,
                    'booked_at' => now(),
                ]);

                BookingItem::create([
                    'booking_id' => $booking->id,
                    'show_seat_id' => $seat->id,
                    'seat_number' => $seat->seat_number,
                    'seat_type_name' => $seat->seatType?->name,
                    'quantity' => 1,
                    'unit_price' => $seat->price,
                    'status' => 'confirmed',
                ]);
            }
        }

        $show->update([
            'available_seats' => $show->seats()->where('status', ShowSeat::STATUS_AVAILABLE)->count(),
        ]);
    }

    private function createGeneralAdmissionDemo(Category $category, Venue $venue): void
    {
        $event = $this->event(
            'Demo Direct Entry Workshop',
            'Direct booking demo. No seat map and no ticket tiers, just choose quantity.',
            $category,
            'https://images.unsplash.com/photo-1515169067865-5387ec356754?auto=format&fit=crop&q=80&w=900'
        );

        Show::query()->updateOrCreate(
            [
                'event_id' => $event->id,
                'venue_id' => $venue->id,
                'show_date' => now()->addDays(14)->toDateString(),
                'show_time' => '11:00:00',
            ],
            [
                'price' => 299,
                'currency_code' => 'INR',
                'available_seats' => 120,
                'sales_capacity' => 120,
                'booking_mode' => 'general_admission',
                'status' => 'scheduled',
                'booking_open_at' => now()->subDay(),
                'booking_close_at' => now()->addDays(14)->setTime(9, 0),
                'seat_lock_minutes' => 10,
            ]
        );
    }

    private function createTieredTicketDemo(Category $category, Venue $venue): void
    {
        $event = $this->event(
            'Demo VIP Normal Festival',
            'Ticket type demo with VIP, Normal, and Early Bird passes without seat selection.',
            $category,
            'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&q=80&w=900'
        );

        $show = Show::query()->updateOrCreate(
            [
                'event_id' => $event->id,
                'venue_id' => $venue->id,
                'show_date' => now()->addDays(21)->toDateString(),
                'show_time' => '18:00:00',
            ],
            [
                'price' => 499,
                'currency_code' => 'INR',
                'available_seats' => 300,
                'sales_capacity' => 300,
                'booking_mode' => 'tiered_tickets',
                'status' => 'scheduled',
                'booking_open_at' => now()->subDay(),
                'booking_close_at' => now()->addDays(21)->setTime(16, 0),
                'seat_lock_minutes' => 10,
            ]
        );

        $show->ticketTypes()->delete();
        $show->ticketTypes()->createMany([
            [
                'name' => 'VIP Pass',
                'code' => 'VIP',
                'description' => 'Premium entry with front zone access.',
                'price' => 1499,
                'capacity' => 50,
                'sold_count' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Normal Pass',
                'code' => 'NORMAL',
                'description' => 'General festival access.',
                'price' => 699,
                'capacity' => 180,
                'sold_count' => 22,
                'is_active' => true,
            ],
            [
                'name' => 'Early Bird',
                'code' => 'EARLY',
                'description' => 'Limited discounted access.',
                'price' => 499,
                'capacity' => 70,
                'sold_count' => 40,
                'is_active' => true,
            ],
        ]);
    }

    private function event(string $title, string $description, Category $category, string $image): Event
    {
        return Event::query()->updateOrCreate(
            ['slug' => Str::slug($title)],
            [
                'title' => $title,
                'description' => $description,
                'category' => $category->name,
                'category_id' => $category->id,
                'slug' => Str::slug($title),
                'poster_image' => $image,
                'banner_image' => $image,
                'language' => 'English',
                'status' => true,
                'publication_status' => 'published',
                'meta_title' => $title,
                'meta_description' => $description,
                'is_featured' => true,
                'published_at' => now(),
            ]
        );
    }

    private function ensureSeatTypes(): void
    {
        collect([
            ['name' => 'VIP', 'code' => 'VIP', 'description' => 'Premium seats', 'color' => '#7c3aed', 'price_multiplier' => 2.2, 'is_active' => true],
            ['name' => 'Gold', 'code' => 'GOLD', 'description' => 'Gold seats', 'color' => '#ca8a04', 'price_multiplier' => 1.6, 'is_active' => true],
            ['name' => 'Silver', 'code' => 'SILVER', 'description' => 'Silver seats', 'color' => '#64748b', 'price_multiplier' => 1.2, 'is_active' => true],
            ['name' => 'Normal', 'code' => 'NORMAL', 'description' => 'Normal seats', 'color' => '#2563eb', 'price_multiplier' => 1, 'is_active' => true],
        ])->each(fn (array $seatType) => SeatType::query()->updateOrCreate(['code' => $seatType['code']], $seatType));
    }
}
