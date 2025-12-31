<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PromoMode extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_enabled',
        'min_percent_of_margin',
        'max_percent_of_margin',
        'priority',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'min_percent_of_margin' => 'float',
        'max_percent_of_margin' => 'float',
        'priority' => 'integer',
    ];
}
