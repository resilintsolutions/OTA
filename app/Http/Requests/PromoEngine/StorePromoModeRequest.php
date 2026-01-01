<?php

declare(strict_types=1);

namespace App\Http\Requests\PromoEngine;

use Illuminate\Foundation\Http\FormRequest;

final class StorePromoModeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_\-]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'is_enabled' => ['sometimes', 'boolean'],
            'min_percent_of_margin' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_percent_of_margin' => ['required', 'numeric', 'min:0', 'max:100', 'gte:min_percent_of_margin'],
            'priority' => ['required', 'integer', 'min:0', 'max:1000'],
        ];
    }
}
