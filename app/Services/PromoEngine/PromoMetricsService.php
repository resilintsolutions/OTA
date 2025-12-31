<?php

declare(strict_types=1);

namespace App\Services\PromoEngine;

use App\Models\PromoEngineSetting;
use App\Models\PromoEvent;
use App\Models\PromoOffer;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class PromoMetricsService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(string $period = 'week'): array
    {
        $settings = PromoEngineSetting::singleton();

        [$start, $end] = $this->periodRange($period);

        $eligibleHotels = $this->eligibleHotelsQuery($settings);
        $eligibleCount = (int) $eligibleHotels->count();

        $activePromosCount = (int) PromoOffer::query()
            ->where('is_active', true)
            ->where('starts_at', '<=', $end)
            ->where('ends_at', '>=', $start)
            ->distinct('hotel_id')
            ->count('hotel_id');

        $avgMarginBefore = $this->avgHotelMargin();
        $avgMarginAfter = $this->avgHotelMarginAfter();

        $impressions = (int) PromoEvent::query()
            ->where('event_type', 'impression')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $clicks = (int) PromoEvent::query()
            ->where('event_type', 'click')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.00;

        [$promoBookings, $nonPromoBookings, $totalBookings] = $this->reservationAttributionCounts($start, $end);

        $promoConversion = $totalBookings > 0 ? round(($promoBookings / $totalBookings) * 100, 2) : 0.00;
        $nonPromoConversion = $totalBookings > 0 ? round(($nonPromoBookings / $totalBookings) * 100, 2) : 0.00;

        return [
            'engine' => [
                'is_enabled' => (bool) $settings->is_enabled,
            ],
            'eligible_properties' => $eligibleCount,
            'properties_with_active_promos' => $activePromosCount,
            'avg_margin_before_promos_percent' => $avgMarginBefore,
            'avg_margin_after_promos_percent' => $avgMarginAfter,
            'promo_bookings' => [
                'promo_impressions' => $impressions,
                'promo_clicks' => $clicks,
                'promo_ctr_percent' => $ctr,
                'promo_conversion_percent' => $promoConversion,
                'non_promo_conversion_percent' => $nonPromoConversion,
                'promo_bookings_count' => $promoBookings,
                'non_promo_bookings_count' => $nonPromoBookings,
                'total_bookings_count' => $totalBookings,
            ],
            'period' => [
                'key' => $period,
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
            ],
        ];
    }

    private function eligibleHotelsQuery(PromoEngineSetting $settings)
    {
        if (! DB::getSchemaBuilder()->hasTable('hotels') || ! DB::getSchemaBuilder()->hasColumn('hotels', 'base_margin_percent')) {
            return DB::table(DB::raw('(select 1 as id where 1=0) as t'));
        }

        return DB::table('hotels')
            ->whereNotNull('base_margin_percent')
            ->where('base_margin_percent', '>=', $settings->min_margin_required_percent);
    }

    private function avgHotelMargin(): float
    {
        if (! DB::getSchemaBuilder()->hasTable('hotels') || ! DB::getSchemaBuilder()->hasColumn('hotels', 'base_margin_percent')) {
            return 0.00;
        }

        $avg = DB::table('hotels')
            ->whereNotNull('base_margin_percent')
            ->avg('base_margin_percent');

        return $avg !== null ? round((float) $avg, 2) : 0.00;
    }

    private function avgHotelMarginAfter(): float
    {
        if (! DB::getSchemaBuilder()->hasTable('hotels') || ! DB::getSchemaBuilder()->hasColumn('hotels', 'base_margin_percent')) {
            return 0.00;
        }

        $now = CarbonImmutable::now();

        $avg = DB::table('hotels')
            ->leftJoin('promo_offers', function ($join) use ($now): void {
                $join->on('promo_offers.hotel_id', '=', 'hotels.id')
                    ->where('promo_offers.is_active', '=', 1)
                    ->where('promo_offers.starts_at', '<=', $now)
                    ->where('promo_offers.ends_at', '>=', $now);
            })
            ->whereNotNull('hotels.base_margin_percent')
            ->selectRaw('AVG(COALESCE(promo_offers.margin_after_percent, hotels.base_margin_percent)) as avg_after')
            ->value('avg_after');

        return $avg !== null ? round((float) $avg, 2) : 0.00;
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private function reservationAttributionCounts(CarbonImmutable $start, CarbonImmutable $end): array
    {
        if (! DB::getSchemaBuilder()->hasTable('reservations') || ! DB::getSchemaBuilder()->hasColumn('reservations', 'promo_offer_id')) {
            return [0, 0, 0];
        }

        $query = DB::table('reservations')
            ->whereBetween('created_at', [$start, $end]);

        // Count conversions as confirmed reservations when status exists.
        if (DB::getSchemaBuilder()->hasColumn('reservations', 'status')) {
            $query->where('status', 'confirmed');
        }

        $total = (int) (clone $query)->count();

        $promo = (int) (clone $query)->whereNotNull('promo_offer_id')->count();
        $nonPromo = $total - $promo;

        return [$promo, $nonPromo, $total];
    }

    /**
     * @return array{0:CarbonImmutable,1:CarbonImmutable}
     */
    private function periodRange(string $period): array
    {
        $now = CarbonImmutable::now();

        return match ($period) {
            'today' => [$now->startOfDay(), $now->endOfDay()],
            'month' => [$now->startOfMonth(), $now->endOfMonth()],
            default => [$now->startOfWeek(), $now->endOfWeek()],
        };
    }
}
