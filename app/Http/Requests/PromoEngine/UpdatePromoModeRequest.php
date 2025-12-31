<?php

declare(strict_types=1);

namespace App\Http\Requests\PromoEngine;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePromoModeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'is_enabled' => ['sometimes', 'boolean'],
            'min_percent_of_margin' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'max_percent_of_margin' => ['sometimes', 'numeric', 'min:0', 'max:100', 'gte:min_percent_of_margin'],
            'priority' => ['sometimes', 'integer', 'min:0', 'max:1000'],
        ];
    }
}
