<?php

declare(strict_types=1);

namespace App\Http\Requests\PromoEngine;

use Illuminate\Foundation\Http\FormRequest;

final class RecomputePromoOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotel_id' => ['required', 'integer', 'min:1'],
            'force' => ['sometimes', 'boolean'],
        ];
    }
}
