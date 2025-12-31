<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoEngine\PromoEngineOverviewRequest;
use App\Http\Requests\PromoEngine\UpdatePromoEngineSettingsRequest;
use App\Http\Requests\PromoEngine\UpdatePromoModeRequest;
use App\Http\Resources\PromoEngine\PromoEngineOverviewResource;
use App\Http\Resources\PromoEngine\PromoModeResource;
use App\Models\PromoEngineSetting;
use App\Models\PromoMode;
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
}
