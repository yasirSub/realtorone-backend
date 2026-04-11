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
            $table->time('morning_reminder_time')->nullable()->default('09:00')->after('notification_enabled');
            $table->time('evening_reminder_time')->nullable()->default('18:00')->after('morning_reminder_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_type_daily_logs', function (Blueprint $table) {
            $table->dropColumn(['morning_reminder_time', 'evening_reminder_time']);
        });
    }
};
