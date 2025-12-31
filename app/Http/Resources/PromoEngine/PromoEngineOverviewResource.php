<?php

declare(strict_types=1);

namespace App\Http\Resources\PromoEngine;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PromoEngineOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->resource;

        return $data;
    }
}
