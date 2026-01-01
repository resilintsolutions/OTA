<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PromoEngine\PromoEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PromoEngineWebController extends Controller
{
    public function recompute(Request $request, PromoEngineService $engine): RedirectResponse
    {
        $hotelId = (int) $request->input('hotel_id', 0);
        if ($hotelId <= 0) {
            return back()->with('error', 'hotel_id is required.');
        }

        $force = (bool) $request->boolean('force', false);

        if ($force) {
            \App\Models\PromoOffer::query()
                ->where('hotel_id', $hotelId)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $offer = $engine->getOrCreateActiveOfferForHotel($hotelId);

        if ($offer === null) {
            return back()->with('error', 'No eligible offer could be generated for this hotel.');
        }

        return back()->with('success', "Offer #{$offer->id} generated for hotel #{$hotelId}.");
    }
}
