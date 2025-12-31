<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_modes', function (Blueprint $table): void {
            $table->id();

            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_enabled')->default(true);

            $table->decimal('min_percent_of_margin', 6, 2);
            $table->decimal('max_percent_of_margin', 6, 2);

            $table->unsignedSmallInteger('priority')->default(0);

            $table->timestamps();

            $table->index(['is_enabled', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_modes');
    }
};
