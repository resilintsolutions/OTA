<?php

declare(strict_types=1);

namespace App\Http\Requests\PromoEngine;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePromoEngineSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_enabled' => ['sometimes', 'boolean'],
            'min_margin_required_percent' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'safety_buffer_percent' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'min_profit_after_promo_percent' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'auto_downgrade_enabled' => ['sometimes', 'boolean'],
            'hide_promo_if_fails_safety' => ['sometimes', 'boolean'],
            'discount_selection_strategy' => ['sometimes', 'string', 'in:max,random'],
            'offer_ttl_minutes' => ['sometimes', 'integer', 'min:1', 'max:10080'],
        ];
    }
}
