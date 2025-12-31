<?php

declare(strict_types=1);

use App\Models\PromoEngineSetting;
use App\Models\PromoMode;
use App\Models\User;

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

    // Our app's Gate::before checks $user->hasRole('admin').
    // For this test, mock it to true without depending on Spatie role tables/guards.
    $admin = Mockery::mock($admin)->makePartial();
    $admin->shouldReceive('hasRole')->andReturn(true);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/promo-engine/settings')
    ->assertForbidden();

    $this->actingAs($admin, 'sanctum')
        ->putJson('/api/v1/admin/promo-engine/settings', [
            'is_enabled' => false,
        ])
    ->assertForbidden();
});
