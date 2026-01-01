<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\PromoEngineSetting;
use App\Models\PromoEvent;
use App\Models\PromoMode;
use App\Models\PromoOffer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class PromoOffersSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('promo_offers')) {
            return;
        }

        // Ensure engine defaults exist
        PromoEngineSetting::singleton();

        // Ensure modes exist (idempotent)
        $this->call(PromoEngineSeeder::class);

        $modes = PromoMode::query()->where('is_enabled', true)->pluck('code')->all();
        if (empty($modes)) {
            $modes = ['light'];
        }

        $hotels = Hotel::query()->limit(10)->get();
        if ($hotels->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        foreach ($hotels as $hotel) {
            $marginBefore = (float) ($hotel->base_margin_percent ?? 12.0);

            // discount between 10%..40% of margin but keep hard floor >= 4
            $maxSafe = max(0.0, $marginBefore - 4.0);
            $discount = min($maxSafe, round($marginBefore * (random_int(10, 40) / 100), 2));
            $discount = max(0.0, $discount);

            $marginAfter = round($marginBefore - $discount, 2);

            // Past offer (inactive)
            PromoOffer::query()->create([
                'hotel_id' => $hotel->id,
                'mode_code' => $modes[array_rand($modes)],
                'discount_percent' => $discount,
                'margin_before_percent' => $marginBefore,
                'margin_after_percent' => $marginAfter,
                'starts_at' => $now->copy()->subDays(10),
                'ends_at' => $now->copy()->subDays(7),
                'is_active' => false,
            ]);

            // Current active offer
            /** @var PromoOffer $active */
            $active = PromoOffer::query()->create([
                'hotel_id' => $hotel->id,
                'mode_code' => $modes[array_rand($modes)],
                'discount_percent' => $discount,
                'margin_before_percent' => $marginBefore,
                'margin_after_percent' => $marginAfter,
                'starts_at' => $now->copy()->subHours(2),
                'ends_at' => $now->copy()->addDays(7),
                'is_active' => true,
            ]);

            // Seed a few events for charts
            if (Schema::hasTable('promo_events')) {
                $impressions = random_int(5, 30);
                $clicks = random_int(0, min(10, $impressions));

                for ($i = 0; $i < $impressions; $i++) {
                    PromoEvent::query()->create([
                        'promo_offer_id' => $active->id,
                        'hotel_id' => $hotel->id,
                        'user_id' => null,
                        'session_id' => 'seed-' . Str::random(10),
                        'event_type' => 'impression',
                        'created_at' => $now->copy()->subDays(random_int(0, 6)),
                        'updated_at' => $now->copy()->subDays(random_int(0, 6)),
                    ]);
                }

                for ($i = 0; $i < $clicks; $i++) {
                    PromoEvent::query()->create([
                        'promo_offer_id' => $active->id,
                        'hotel_id' => $hotel->id,
                        'user_id' => null,
                        'session_id' => 'seed-' . Str::random(10),
                        'event_type' => 'click',
                        'created_at' => $now->copy()->subDays(random_int(0, 6)),
                        'updated_at' => $now->copy()->subDays(random_int(0, 6)),
                    ]);
                }
            }
        }
    }
}
