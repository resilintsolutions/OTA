<?php

declare(strict_types=1);

use App\Models\PromoEngineSetting;
use App\Models\PromoEvent;
use App\Models\PromoMode;
use App\Models\PromoOffer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    PromoEngineSetting::singleton();

    PromoMode::query()->create([
        'code' => 'light',
        'name' => 'Light',
        'is_enabled' => true,
        'min_percent_of_margin' => 10.00,
        'max_percent_of_margin' => 20.00,
        'priority' => 100,
    ]);

});

it('blocks non-admin from settings endpoint', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/admin/promo-engine/settings')
        ->assertForbidden();
});

it('allows admin to view and update settings', function (): void {
    $admin = User::factory()->create();

    Gate::shouldReceive('authorize')->andReturnTrue();

    // Our app's Gate::before checks $user->hasRole('admin').
    // For this test, mock it to true without depending on Spatie role tables/guards.
    $admin = Mockery::mock($admin)->makePartial();
    $admin->shouldReceive('hasRole')->andReturn(true);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/promo-engine/settings')
        ->assertOk();

    $this->actingAs($admin, 'sanctum')
        ->putJson('/api/v1/admin/promo-engine/settings', [
            'is_enabled' => false,
        ])
        ->assertOk()
        ->assertJsonPath('settings.is_enabled', false);
});

it('allows admin to create and delete a promo mode', function (): void {
    $admin = User::factory()->create();

    Gate::shouldReceive('authorize')->andReturnTrue();

    $create = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/admin/promo-engine/modes', [
            'code' => 'test_mode',
            'name' => 'Test Mode',
            'is_enabled' => true,
            'min_percent_of_margin' => 5,
            'max_percent_of_margin' => 10,
            'priority' => 150,
        ]);

    $create->assertCreated();
    $modeId = (int) $create->json('mode.id');

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/admin/promo-engine/modes/{$modeId}")
        ->assertOk();
});

it('allows admin to list offers and events', function (): void {
    $admin = User::factory()->create();

    Gate::shouldReceive('authorize')->andReturnTrue();

    $hotelId = DB::table('hotels')->insertGetId([
        'name' => 'Test Hotel',
        'slug' => 'test-hotel-admin',
        'base_margin_percent' => 10.00,
    ]);

    /** @var PromoOffer $offer */
    $offer = PromoOffer::query()->create([
        'hotel_id' => $hotelId,
        'mode_code' => 'light',
        'discount_percent' => 2.00,
        'margin_before_percent' => 10.00,
        'margin_after_percent' => 8.00,
        'starts_at' => now()->subMinute(),
        'ends_at' => now()->addMinute(),
        'is_active' => true,
    ]);

    PromoEvent::query()->create([
        'promo_offer_id' => $offer->id,
        'hotel_id' => $hotelId,
        'user_id' => null,
        'session_id' => 'sess1',
        'event_type' => 'impression',
    ]);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/promo-engine/offers?hotel_id='.$hotelId)
        ->assertOk();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/promo-engine/events?hotel_id='.$hotelId)
        ->assertOk();
});
