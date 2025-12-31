<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations')) {
            return;
        }

        if (! Schema::hasColumn('reservations', 'promo_offer_id')) {
            Schema::table('reservations', function (Blueprint $table): void {
                $table->unsignedBigInteger('promo_offer_id')->nullable()->after('id');
                $table->index('promo_offer_id');
            });

            if (Schema::hasTable('promo_offers')) {
                Schema::table('reservations', function (Blueprint $table): void {
                    $table->foreign('promo_offer_id')->references('id')->on('promo_offers')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('reservations')) {
            return;
        }

        if (Schema::hasColumn('reservations', 'promo_offer_id')) {
            Schema::table('reservations', function (Blueprint $table): void {
                try {
                    $table->dropForeign(['promo_offer_id']);
                } catch (Throwable $e) {
                    // Ignore if FK doesn't exist.
                }

                $table->dropIndex(['promo_offer_id']);
                $table->dropColumn('promo_offer_id');
            });
        }
    }
};
