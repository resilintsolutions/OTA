<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hotels')) {
            return;
        }

        if (! Schema::hasColumn('hotels', 'base_margin_percent')) {
            Schema::table('hotels', function (Blueprint $table): void {
                $table->decimal('base_margin_percent', 6, 2)->nullable()->after('id');
                $table->index('base_margin_percent');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('hotels')) {
            return;
        }

        if (Schema::hasColumn('hotels', 'base_margin_percent')) {
            Schema::table('hotels', function (Blueprint $table): void {
                $table->dropIndex(['base_margin_percent']);
                $table->dropColumn('base_margin_percent');
            });
        }
    }
};
