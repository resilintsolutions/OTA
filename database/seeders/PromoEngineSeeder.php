<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PromoEngineSetting;
use App\Models\PromoMode;
use Illuminate\Database\Seeder;

final class PromoEngineSeeder extends Seeder
{
    public function run(): void
    {
        PromoEngineSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'is_enabled' => true,
                'min_margin_required_percent' => 6.00,
                'safety_buffer_percent' => 2.00,
                'min_profit_after_promo_percent' => 4.00,
                'auto_downgrade_enabled' => true,
                'hide_promo_if_fails_safety' => true,
                'discount_selection_strategy' => 'max',
                'offer_ttl_minutes' => 1440,
            ]
        );

        PromoMode::query()->updateOrCreate(
            ['code' => 'aggressive'],
            [
                'name' => 'Aggressive',
                'is_enabled' => true,
                'min_percent_of_margin' => 41.00,
                'max_percent_of_margin' => 70.00,
                'priority' => 300,
            ]
        );

        PromoMode::query()->updateOrCreate(
            ['code' => 'normal'],
            [
                'name' => 'Normal',
                'is_enabled' => true,
                'min_percent_of_margin' => 21.00,
                'max_percent_of_margin' => 40.00,
                'priority' => 200,
            ]
        );

        PromoMode::query()->updateOrCreate(
            ['code' => 'light'],
            [
                'name' => 'Light',
                'is_enabled' => true,
                'min_percent_of_margin' => 10.00,
                'max_percent_of_margin' => 20.00,
                'priority' => 100,
            ]
        );
    }
}
