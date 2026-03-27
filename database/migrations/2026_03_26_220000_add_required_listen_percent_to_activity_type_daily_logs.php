<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_type_daily_logs', function (Blueprint $table) {
            $table->unsignedTinyInteger('required_listen_percent')
                ->default(0)
                ->after('audio_url');
        });
    }

    public function down(): void
    {
        Schema::table('activity_type_daily_logs', function (Blueprint $table) {
            $table->dropColumn('required_listen_percent');
        });
    }
};
