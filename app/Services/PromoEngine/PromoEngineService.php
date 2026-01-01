<?php

declare(strict_types=1);

namespace App\Services\PromoEngine;

use App\Models\PromoEngineSetting;
use App\Models\PromoMode;
use App\Models\PromoOffer;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class PromoEngineService
{
    public function getOrCreateActiveOfferForHotel(int $hotelId): ?PromoOffer
    {
        $settings = PromoEngineSetting::singleton();

        if (! $settings->is_enabled) {
            return null;
        }

        $now = CarbonImmutable::now();

        /** @var PromoOffer|null $existing */
        $existing = PromoOffer::query()
            ->where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->latest('id')
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $margin = $this->getHotelBaseMarginPercent($hotelId);
        if ($margin === null) {
            return null;
        }

        return $this->computeAndPersistOffer($hotelId, $margin, $settings);
    }

    public function computeAndPersistOffer(int $hotelId, float $marginBeforePercent, PromoEngineSetting $settings): ?PromoOffer
    {
        if ($marginBeforePercent < $settings->min_margin_required_percent) {
            return null;
        }

        $modes = PromoMode::query()
            ->where('is_enabled', true)
            ->orderByDesc('priority')
            ->get();

        if ($modes->isEmpty()) {
            return null;
        }

        $now = CarbonImmutable::now();
        $ttlMinutes = max(1, (int) $settings->offer_ttl_minutes);
        $endsAt = $now->addMinutes($ttlMinutes);

        foreach ($modes as $mode) {
            $computed = $this->tryMode($mode, $marginBeforePercent, $settings);

            if ($computed !== null) {
                PromoOffer::query()
                    ->where('hotel_id', $hotelId)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

                /** @var PromoOffer $offer */
                $offer = PromoOffer::query()->create([
                    'hotel_id' => $hotelId,
                    'mode_code' => $mode->code,
                    'discount_percent' => $computed['discount_percent'],
                    'margin_before_percent' => $marginBeforePercent,
                    'margin_after_percent' => $computed['margin_after_percent'],
                    'starts_at' => $now,
                    'ends_at' => $endsAt,
                    'is_active' => true,
                ]);

                return $offer;
            }

            if (! $settings->auto_downgrade_enabled) {
                break;
            }
        }

        return null;
    }

    /**
     * @return array{discount_percent: float, margin_after_percent: float}|null
     */
    private function tryMode(PromoMode $mode, float $marginBeforePercent, PromoEngineSetting $settings): ?array
    {
        $rangeMin = ($marginBeforePercent * $mode->min_percent_of_margin) / 100.0;
        $rangeMax = ($marginBeforePercent * $mode->max_percent_of_margin) / 100.0;

    // Hard floor: final margin can never drop below this value.
    // Safety buffer is treated as an *ideal* target (soft), not a hard rejection.
    $hardFloor = $settings->min_profit_after_promo_percent;
    $maxSafeDiscount = max(0.0, $marginBeforePercent - $hardFloor);

        $candidateMax = min($rangeMax, $maxSafeDiscount);

        if ($candidateMax < $rangeMin) {
            return null;
        }

        $discount = $this->selectDiscount($settings->discount_selection_strategy, $rangeMin, $candidateMax);

        $marginAfter = $marginBeforePercent - $discount;

        if ($marginAfter < $settings->min_profit_after_promo_percent) {
            return null;
        }

        return [
            'discount_percent' => round($discount, 2),
            'margin_after_percent' => round($marginAfter, 2),
        ];
    }

    private function selectDiscount(string $strategy, float $min, float $max): float
    {
        $min = max(0.0, $min);
        $max = max($min, $max);

        if ($strategy === 'random') {
            $minInt = (int) round($min * 100);
            $maxInt = (int) round($max * 100);
            $pick = random_int($minInt, $maxInt);

            return $pick / 100.0;
        }

        return $max;
    }

    private function getHotelBaseMarginPercent(int $hotelId): ?float
    {
        if (! DB::getSchemaBuilder()->hasTable('hotels')) {
            return null;
        }

        if (! DB::getSchemaBuilder()->hasColumn('hotels', 'base_margin_percent')) {
            return null;
        }

        $value = DB::table('hotels')
            ->where('id', $hotelId)
            ->value('base_margin_percent');

        if ($value === null) {
            return null;
        }

        return (float) $value;
    }
}
