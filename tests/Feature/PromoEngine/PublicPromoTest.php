<?php

declare(strict_types=1);

use App\Models\PromoEngineSetting;
use App\Models\PromoMode;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    PromoEngineSetting::singleton();

    PromoMode::query()->create([
        'code' => 'aggressive',
        'name' => 'Aggressive',
        'is_enabled' => true,
        'min_percent_of_margin' => 41.00,
        'max_percent_of_margin' => 70.00,
        'priority' => 300,
    ]);

    PromoMode::query()->create([
        'code' => 'normal',
        'name' => 'Normal',
        'is_enabled' => true,
        'min_percent_of_margin' => 21.00,
        'max_percent_of_margin' => 40.00,
        'priority' => 200,
    ]);

    PromoMode::query()->create([
        'code' => 'light',
        'name' => 'Light',
        'is_enabled' => true,
        'min_percent_of_margin' => 10.00,
        'max_percent_of_margin' => 20.00,
        'priority' => 100,
    ]);
});

it('returns an offer for eligible hotel', function (): void {
    $hotelId = DB::table('hotels')->insertGetId([
        'name' => 'Test Hotel',
        'slug' => 'test-hotel',
        'base_margin_percent' => 10.00,
    ]);

    $res = $this->getJson("/api/v1/promos/offer?hotel_id={$hotelId}");
    $res->assertOk();
    $res->assertJsonPath('data.hotel_id', $hotelId);
});

it('returns null for missing hotel_id', function (): void {
    $res = $this->getJson('/api/v1/promos/offer');
    $res->assertStatus(422);
});
