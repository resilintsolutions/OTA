<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_engine_settings', function (Blueprint $table): void {
            $table->id();

            $table->boolean('is_enabled')->default(true);

            $table->decimal('min_margin_required_percent', 6, 2)->default(6.00);
            $table->decimal('safety_buffer_percent', 6, 2)->default(2.00);
            $table->decimal('min_profit_after_promo_percent', 6, 2)->default(4.00);

            $table->boolean('auto_downgrade_enabled')->default(true);
            $table->boolean('hide_promo_if_fails_safety')->default(true);

            $table->enum('discount_selection_strategy', ['max', 'random'])->default('max');
            $table->unsignedInteger('offer_ttl_minutes')->default(1440);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_engine_settings');
    }
};
