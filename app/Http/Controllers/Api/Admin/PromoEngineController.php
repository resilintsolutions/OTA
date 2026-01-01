<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoEngine\ListPromoEventsRequest;
use App\Http\Requests\PromoEngine\ListPromoOffersRequest;
use App\Http\Requests\PromoEngine\PromoEngineOverviewRequest;
use App\Http\Requests\PromoEngine\RecomputePromoOfferRequest;
use App\Http\Requests\PromoEngine\StorePromoModeRequest;
use App\Http\Requests\PromoEngine\UpdatePromoEngineSettingsRequest;
use App\Http\Requests\PromoEngine\UpdatePromoModeRequest;
use App\Http\Resources\PromoEngine\PromoEventResource;
use App\Http\Resources\PromoEngine\PromoEngineOverviewResource;
use App\Http\Resources\PromoEngine\PromoModeResource;
use App\Http\Resources\PromoEngine\PromoOfferResource;
use App\Models\PromoEngineSetting;
use App\Models\PromoEvent;
use App\Models\PromoMode;
use App\Models\PromoOffer;
use App\Services\PromoEngine\PromoEngineService;
use App\Services\PromoEngine\PromoMetricsService;
use Illuminate\Http\JsonResponse;

final class PromoEngineController extends Controller
{
    public function overview(PromoEngineOverviewRequest $request, PromoMetricsService $metrics): PromoEngineOverviewResource
    {
        $this->authorize('viewAny', PromoEngineSetting::class);

        $period = (string) ($request->validated()['period'] ?? 'week');

        return new PromoEngineOverviewResource($metrics->overview($period));
    }

    public function settings(): JsonResponse
    {
        $this->authorize('viewAny', PromoEngineSetting::class);

        $settings = PromoEngineSetting::singleton();
        $modes = PromoMode::query()->orderByDesc('priority')->get();

        return response()->json([
            'settings' => $settings,
            'modes' => PromoModeResource::collection($modes),
        ]);
    }

    public function updateSettings(UpdatePromoEngineSettingsRequest $request): JsonResponse
    {
        $this->authorize('update', PromoEngineSetting::class);

        $settings = PromoEngineSetting::singleton();
        $settings->fill($request->validated());
        $settings->save();

        return response()->json([
            'message' => 'Promo engine settings updated.',
            'settings' => $settings,
        ]);
    }

    public function updateMode(UpdatePromoModeRequest $request, PromoMode $mode): JsonResponse
    {
        $this->authorize('update', PromoEngineSetting::class);

        $mode->fill($request->validated());
        $mode->save();

        return response()->json([
            'message' => 'Promo mode updated.',
            'mode' => new PromoModeResource($mode),
        ]);
    }

    public function storeMode(StorePromoModeRequest $request): JsonResponse
    {
        $this->authorize('update', PromoEngineSetting::class);

        /** @var array<string, mixed> $data */
        $data = $request->validated();

        if (PromoMode::query()->where('code', $data['code'])->exists()) {
            return response()->json(['message' => 'Mode code already exists.'], 422);
        }

        /** @var PromoMode $mode */
        $mode = PromoMode::query()->create([
            'code' => $data['code'],
            'name' => $data['name'],
            'is_enabled' => (bool) ($data['is_enabled'] ?? true),
            'min_percent_of_margin' => (float) $data['min_percent_of_margin'],
            'max_percent_of_margin' => (float) $data['max_percent_of_margin'],
            'priority' => (int) $data['priority'],
        ]);

        return response()->json([
            'message' => 'Promo mode created.',
            'mode' => new PromoModeResource($mode),
        ], 201);
    }

    public function destroyMode(PromoMode $mode): JsonResponse
    {
        $this->authorize('update', PromoEngineSetting::class);

        $deleted = (bool) $mode->delete();

        return response()->json([
            'message' => $deleted ? 'Promo mode deleted.' : 'Promo mode could not be deleted.',
        ]);
    }

    public function offers(ListPromoOffersRequest $request): JsonResponse
    {
        $this->authorize('viewAny', PromoEngineSetting::class);

        $data = $request->validated();
        $perPage = (int) ($data['per_page'] ?? 50);

        $query = PromoOffer::query()->orderByDesc('id');

        if (isset($data['hotel_id'])) {
            $query->where('hotel_id', (int) $data['hotel_id']);
        }
        if (array_key_exists('is_active', $data)) {
            $query->where('is_active', (bool) $data['is_active']);
        }
        if (isset($data['mode_code'])) {
            $query->where('mode_code', (string) $data['mode_code']);
        }

        $paginated = $query->paginate($perPage);

        return response()->json([
            'data' => PromoOfferResource::collection($paginated->items()),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    public function deactivateOffer(PromoOffer $offer): JsonResponse
    {
        $this->authorize('update', PromoEngineSetting::class);

        $offer->update(['is_active' => false]);

        return response()->json([
            'message' => 'Offer deactivated.',
            'offer' => new PromoOfferResource($offer->fresh()),
        ]);
    }

    public function recomputeOffer(RecomputePromoOfferRequest $request, PromoEngineService $engine): JsonResponse
    {
        $this->authorize('update', PromoEngineSetting::class);

        $data = $request->validated();
        $hotelId = (int) $data['hotel_id'];
        $force = (bool) ($data['force'] ?? false);

        if ($force) {
            PromoOffer::query()->where('hotel_id', $hotelId)->where('is_active', true)->update(['is_active' => false]);
        }

        $offer = $engine->getOrCreateActiveOfferForHotel($hotelId);

        return response()->json([
            'data' => $offer ? new PromoOfferResource($offer) : null,
        ]);
    }

    public function events(ListPromoEventsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', PromoEngineSetting::class);

        $data = $request->validated();
        $perPage = (int) ($data['per_page'] ?? 50);

        $query = PromoEvent::query()->orderByDesc('id');

        if (isset($data['hotel_id'])) {
            $query->where('hotel_id', (int) $data['hotel_id']);
        }
        if (isset($data['promo_offer_id'])) {
            $query->where('promo_offer_id', (int) $data['promo_offer_id']);
        }
        if (isset($data['event_type'])) {
            $query->where('event_type', (string) $data['event_type']);
        }

        if (isset($data['period'])) {
            $period = (string) $data['period'];
            $now = now();
            [$start, $end] = match ($period) {
                'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
                'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
                default => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            };
            $query->whereBetween('created_at', [$start, $end]);
        }

        $paginated = $query->paginate($perPage);

        return response()->json([
            'data' => PromoEventResource::collection($paginated->items()),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }
}
