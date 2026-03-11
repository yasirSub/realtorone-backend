<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_metrics', function (Blueprint $table) {
            if (!Schema::hasColumn('performance_metrics', 'metadata')) {
                $table->json('metadata')->nullable()->after('streak_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('performance_metrics', function (Blueprint $table) {
            if (Schema::hasColumn('performance_metrics', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};