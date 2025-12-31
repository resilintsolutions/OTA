<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PromoEngineSetting extends Model
{
    protected $table = 'promo_engine_settings';

    protected $fillable = [
        'is_enabled',
        'min_margin_required_percent',
        'safety_buffer_percent',
        'min_profit_after_promo_percent',
        'auto_downgrade_enabled',
        'hide_promo_if_fails_safety',
        'discount_selection_strategy',
        'offer_ttl_minutes',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'min_margin_required_percent' => 'float',
        'safety_buffer_percent' => 'float',
        'min_profit_after_promo_percent' => 'float',
        'auto_downgrade_enabled' => 'boolean',
        'hide_promo_if_fails_safety' => 'boolean',
        'offer_ttl_minutes' => 'integer',
    ];

    public static function singleton(): self
    {
        /** @var self $settings */
        $settings = self::query()->firstOrCreate(['id' => 1], [
            'is_enabled' => true,
        ]);

        return $settings;
    }
}
