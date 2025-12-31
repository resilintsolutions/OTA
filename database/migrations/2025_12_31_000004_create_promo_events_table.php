<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_events', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('promo_offer_id');
            $table->unsignedBigInteger('hotel_id');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id', 128)->nullable();

            $table->enum('event_type', ['impression', 'click']);

            $table->timestamps();

            $table->index(['promo_offer_id', 'event_type', 'created_at']);
            $table->index(['hotel_id', 'event_type', 'created_at']);
            $table->index(['session_id', 'event_type', 'created_at']);

            $table->foreign('promo_offer_id')->references('id')->on('promo_offers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_events');
    }
};
