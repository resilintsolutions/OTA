<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MspSetting;
use App\Models\MarginRulesSetting;

class PricingDefaultsSeeder extends Seeder
{
    public function run()
    {
        // Global MSP default
        MspSetting::updateOrCreate(
            ['scope' => 'global', 'country' => null, 'city' => null],
            ['msp_amount' => 45.00, 'currency' => 'USD']
        );

        // Global margin rules default
        MarginRulesSetting::updateOrCreate(
            ['scope' => 'global', 'country' => null, 'city' => null],
            [
                'default_margin_percent' => 10.00,
                'min_margin_percent'     => 5.00,
                'max_margin_percent'     => 25.00,
            ]
        );
    }
}
