<?php

use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('loads admin kpis page on sqlite without datediff error', function () {
    // Ensure the controller hits the avg nights computation path.
    $hotel = Hotel::query()->create([
        'name' => 'KPI Test Hotel',
        'slug' => 'kpi-test-hotel',
        'country' => 'AE',
        'city' => 'Dubai',
    ]);

    Reservation::query()->create([
        'hotel_id' => $hotel->id,
    'guest_info' => ['name' => 'Test Guest'],
        'status' => 'confirmed',
        'total_price' => 100,
        'check_in' => Carbon::today()->toDateString(),
        'check_out' => Carbon::today()->addDays(3)->toDateString(),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);

    $admin = User::factory()->create();
    // Satisfy `role:admin` middleware in this app.
    Role::findOrCreate('admin', 'web');
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/kpis');

    $response->assertOk();
    // basic sanity that the view rendered and includes the KPI label
    $response->assertSee('Average Nights', false);
});
