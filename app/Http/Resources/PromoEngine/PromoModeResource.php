<?php

declare(strict_types=1);

namespace App\Http\Resources\PromoEngine;

use App\Models\PromoMode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PromoMode
 */
final class PromoModeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var PromoMode $mode */
        $mode = $this->resource;

        return [
            'id' => $mode->id,
            'code' => $mode->code,
            'name' => $mode->name,
            'is_enabled' => $mode->is_enabled,
            'min_percent_of_margin' => $mode->min_percent_of_margin,
            'max_percent_of_margin' => $mode->max_percent_of_margin,
            'priority' => $mode->priority,
        ];
    }
}
