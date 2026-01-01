<?php

declare(strict_types=1);

namespace App\Http\Requests\PromoEngine;

use Illuminate\Foundation\Http\FormRequest;

final class ListPromoEventsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotel_id' => ['sometimes', 'integer', 'min:1'],
            'promo_offer_id' => ['sometimes', 'integer', 'min:1'],
            'event_type' => ['sometimes', 'string', 'max:50'],
            'period' => ['sometimes', 'string', 'in:today,week,month'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ];
    }
}
