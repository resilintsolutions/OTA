<?php

declare(strict_types=1);

namespace App\Http\Resources\PromoEngine;

use App\Models\PromoEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PromoEvent
 */
final class PromoEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var PromoEvent $event */
        $event = $this->resource;

        return [
            'id' => $event->id,
            'promo_offer_id' => $event->promo_offer_id,
            'hotel_id' => $event->hotel_id,
            'user_id' => $event->user_id,
            'session_id' => $event->session_id,
            'event_type' => $event->event_type,
            'created_at' => $event->created_at?->toIso8601String(),
        ];
    }
}
