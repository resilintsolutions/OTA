<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoEngine\TrackPromoEventRequest;
use App\Http\Resources\PromoEngine\PromoOfferResource;
use App\Models\PromoEvent;
use App\Models\PromoOffer;
use App\Services\PromoEngine\PromoEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PromoController extends Controller
{
    public function offerForHotel(Request $request, PromoEngineService $engine): JsonResponse
    {
        $hotelId = (int) $request->query('hotel_id', 0);
        if ($hotelId <= 0) {
            return response()->json(['message' => 'hotel_id is required.'], 422);
        }

        $offer = $engine->getOrCreateActiveOfferForHotel($hotelId);

        if ($offer === null) {
            return response()->json(['data' => null]);
        }

        return response()->json(['data' => new PromoOfferResource($offer)]);
    }

    public function track(TrackPromoEventRequest $request, PromoOffer $offer): JsonResponse
    {
        $data = $request->validated();

        PromoEvent::query()->create([
            'promo_offer_id' => $offer->id,
            'hotel_id' => $offer->hotel_id,
            'user_id' => $request->user()?->id,
            'session_id' => $data['session_id'] ?? null,
            'event_type' => $data['event_type'],
        ]);

        return response()->json(['message' => 'Tracked.']);
    }
}
