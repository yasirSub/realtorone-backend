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
            $table->boolean('morning_reminder_enabled')->default(true)->after('notification_enabled');
            $table->boolean('evening_reminder_enabled')->default(true)->after('morning_reminder_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_type_daily_logs', function (Blueprint $table) {
            $table->dropColumn(['morning_reminder_enabled', 'evening_reminder_enabled']);
        });
    }
};
