<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_offers', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('hotel_id');

            $table->string('mode_code');

            $table->decimal('discount_percent', 6, 2);

            $table->decimal('margin_before_percent', 6, 2);
            $table->decimal('margin_after_percent', 6, 2);

            $table->timestamp('starts_at');
            $table->timestamp('ends_at');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['hotel_id', 'is_active', 'starts_at', 'ends_at']);
            $table->index(['mode_code', 'is_active']);

            // Must be inside the create callback to register FK.
            $table->foreign('hotel_id')->references('id')->on('hotels')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_offers');
    }
};
