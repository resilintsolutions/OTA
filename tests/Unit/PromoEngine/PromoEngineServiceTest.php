<?php

declare(strict_types=1);

use App\Models\PromoEngineSetting;
use App\Models\PromoMode;
use App\Services\PromoEngine\PromoEngineService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses(Tests\TestCase::class);

beforeEach(function (): void {
    PromoEngineSetting::singleton()->update([
        'is_enabled' => true,
        'min_margin_required_percent' => 6.00,
        'safety_buffer_percent' => 2.00,
        'min_profit_after_promo_percent' => 4.00,
        'auto_downgrade_enabled' => true,
        'discount_selection_strategy' => 'max',
        'offer_ttl_minutes' => 60,
    ]);

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

it('returns null when engine is off', function (): void {
    PromoEngineSetting::singleton()->update(['is_enabled' => false]);

    $hotelId = DB::table('hotels')->insertGetId([
        'name' => 'Test Hotel',
        'slug' => 'test-hotel',
        'base_margin_percent' => 12.00,
    ]);

    $engine = app(PromoEngineService::class);
    expect($engine->getOrCreateActiveOfferForHotel($hotelId))->toBeNull();
});

it('blocks hotels below min margin required (6%)', function (): void {
    $hotelId = DB::table('hotels')->insertGetId([
        'name' => 'Test Hotel',
        'slug' => 'test-hotel',
        'base_margin_percent' => 5.50,
    ]);

    $engine = app(PromoEngineService::class);
    expect($engine->getOrCreateActiveOfferForHotel($hotelId))->toBeNull();
});

it('auto-downgrades when aggressive would break min profit after promo', function (): void {
    // Margin 12%: aggressive safe max discount is 8.0 (hardFloor 4%) => leaves 4.0 and remains aggressive.
    $hotelId = DB::table('hotels')->insertGetId([
        'name' => 'Test Hotel',
        'slug' => 'test-hotel',
        'base_margin_percent' => 12.00,
    ]);

    $engine = app(PromoEngineService::class);
    $offer = $engine->getOrCreateActiveOfferForHotel($hotelId);

    expect($offer)->not->toBeNull();
    expect($offer->mode_code)->toBe('aggressive');
    expect($offer->discount_percent)->toBe(8.00);
    expect($offer->margin_after_percent)->toBe(4.00);
});

it('returns null when auto-downgrade is disabled and first mode fails', function (): void {
    PromoEngineSetting::singleton()->update(['auto_downgrade_enabled' => false]);

    // Use a margin where aggressive cannot meet its minimum range after safety.
    // Margin 6.0: aggressive min range = 2.46, but maxSafeDiscount = 2.0 => fails.
    $hotelId = DB::table('hotels')->insertGetId([
        'name' => 'Test Hotel',
        'slug' => 'test-hotel',
        'base_margin_percent' => 6.00,
    ]);

    $engine = app(PromoEngineService::class);
    $offer = $engine->getOrCreateActiveOfferForHotel($hotelId);

    expect($offer)->toBeNull();
});
