<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PromoOffer extends Model
{
    protected $fillable = [
        'hotel_id',
        'mode_code',
        'discount_percent',
        'margin_before_percent',
        'margin_after_percent',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'discount_percent' => 'float',
        'margin_before_percent' => 'float',
        'margin_after_percent' => 'float',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(PromoEvent::class, 'promo_offer_id');
    }
}
