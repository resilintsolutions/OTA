<?php

declare(strict_types=1);

namespace App\Http\Resources\PromoEngine;

use App\Models\PromoOffer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PromoOffer
 */
final class PromoOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var PromoOffer $offer */
        $offer = $this->resource;

        return [
            'id' => $offer->id,
            'hotel_id' => $offer->hotel_id,
            'mode' => $offer->mode_code,
            'discount_percent' => $offer->discount_percent,
            'margin_before_percent' => $offer->margin_before_percent,
            'margin_after_percent' => $offer->margin_after_percent,
            'starts_at' => $offer->starts_at?->toIso8601String(),
            'ends_at' => $offer->ends_at?->toIso8601String(),
        ];
    }
}
