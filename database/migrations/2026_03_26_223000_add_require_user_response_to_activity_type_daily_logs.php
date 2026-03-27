<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_type_daily_logs', function (Blueprint $table) {
            $table->boolean('require_user_response')
                ->default(false)
                ->after('required_listen_percent');
        });
    }

    public function down(): void
    {
        Schema::table('activity_type_daily_logs', function (Blueprint $table) {
            $table->dropColumn('require_user_response');
        });
    }
};
