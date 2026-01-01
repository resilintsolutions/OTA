<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Roles + baseline admin/customer/agent (idempotent)
        // Use explicit guard to satisfy web middleware.
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('customer', 'web');
        Role::findOrCreate('agent', 'web');

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => Hash::make('secret123')]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $customer = User::query()->firstOrCreate(
            ['email' => 'customer@example.com'],
            ['name' => 'Demo Customer', 'password' => Hash::make('secret123')]
        );
        if (! $customer->hasRole('customer')) {
            $customer->assignRole('customer');
        }

        // Hotels + rooms (+ promo base margin)
        $hotelsSeed = [
            ['name' => 'Demo Beach Resort', 'country' => 'AE', 'city' => 'Dubai', 'base_margin_percent' => 18.0],
            ['name' => 'Demo City Hotel', 'country' => 'GB', 'city' => 'London', 'base_margin_percent' => 12.0],
            ['name' => 'Demo Mountain Lodge', 'country' => 'CH', 'city' => 'Zermatt', 'base_margin_percent' => 22.0],
            ['name' => 'Demo Budget Inn', 'country' => 'IN', 'city' => 'Mumbai', 'base_margin_percent' => 8.0],
            ['name' => 'Demo Luxury Suites', 'country' => 'US', 'city' => 'New York', 'base_margin_percent' => 25.0],
        ];

        foreach ($hotelsSeed as $h) {
            $slug = Str::slug($h['name']);

            $hotel = Hotel::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $h['name'],
                    'country' => $h['country'],
                    'city' => $h['city'],
                    'vendor' => 'local',
                    'lowest_rate' => 120,
                    'currency' => 'USD',
                    'description' => 'Demo hotel for testing flows.',
                    'status' => 'active',
                    'meta' => ['seed' => 'demo'],
                ]
            );

            // Optional column added by promo engine migration
            if (\Illuminate\Support\Facades\Schema::hasColumn('hotels', 'base_margin_percent')) {
                $hotel->forceFill(['base_margin_percent' => $h['base_margin_percent']])->save();
            }

            // create or update a couple rooms
            $rooms = [
                ['name' => 'Standard Room', 'price_per_night' => 140, 'refundable' => true, 'availability' => 5],
                ['name' => 'Deluxe Room', 'price_per_night' => 220, 'refundable' => false, 'availability' => 3],
            ];

            foreach ($rooms as $r) {
                Room::query()->updateOrCreate(
                    ['hotel_id' => $hotel->id, 'name' => $r['name']],
                    [
                        'vendor_room_id' => null,
                        'price_per_night' => $r['price_per_night'],
                        'refundable' => $r['refundable'],
                        'availability' => $r['availability'],
                        'amenities' => ['wifi', 'ac'],
                    ]
                );
            }

            // Create a few reservations per hotel for KPIs/dashboard.
            // Reservation has required guest_info.
            $room = $hotel->rooms()->first();
            if ($room) {
                for ($i = 0; $i < 3; $i++) {
                    $checkIn = Carbon::today()->addDays($i * 2);
                    $nights = 1 + $i;
                    $checkOut = (clone $checkIn)->addDays($nights);

                    Reservation::query()->create([
                        'confirmation_number' => 'DEMO-' . strtoupper(Str::random(8)),
                        'hotel_id' => $hotel->id,
                        'room_id' => $room->id,
                        'guest_info' => [
                            'name' => 'Test Guest ' . ($i + 1),
                            'email' => 'guest' . ($i + 1) . '@example.com',
                            'country' => $h['country'],
                        ],
                        'total_price' => $room->price_per_night ? ($room->price_per_night * $nights) : (100 * $nights),
                        'currency' => 'USD',
                        'status' => $i === 0 ? 'confirmed' : ($i === 1 ? 'pending' : 'cancelled'),
                        'channel' => $i % 2 === 0 ? 'web' : 'mobile',
                        'check_in' => $checkIn->toDateString(),
                        'check_out' => $checkOut->toDateString(),
                        'raw_response' => ['seed' => 'demo'],
                        'created_at' => Carbon::now()->subDays($i),
                        'updated_at' => Carbon::now()->subDays($i),
                    ]);
                }
            }
        }

        // Promo Engine defaults (idempotent)
        $this->call(PromoEngineSeeder::class);

    // Mock promo offers + events for admin dashboards
    $this->call(PromoOffersSeeder::class);
    }
}
