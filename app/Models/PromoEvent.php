<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PromoEvent extends Model
{
    protected $fillable = [
        'promo_offer_id',
        'hotel_id',
        'user_id',
        'session_id',
        'event_type',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(PromoOffer::class, 'promo_offer_id');
    }
}
