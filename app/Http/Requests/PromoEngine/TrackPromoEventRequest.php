<?php

declare(strict_types=1);

namespace App\Http\Requests\PromoEngine;

use Illuminate\Foundation\Http\FormRequest;

final class TrackPromoEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', 'string', 'in:impression,click'],
            'session_id' => ['sometimes', 'nullable', 'string', 'max:128'],
        ];
    }
}
