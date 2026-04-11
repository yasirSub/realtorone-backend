<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_type_daily_logs', function (Blueprint $table) {
            $table->time('reminder_time')->default('18:00')->after('notification_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_type_daily_logs', function (Blueprint $table) {
            $table->dropColumn('reminder_time');
        });
    }
};
