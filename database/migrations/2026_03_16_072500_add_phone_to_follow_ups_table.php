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
        if (Schema::hasTable('follow_ups') && ! Schema::hasColumn('follow_ups', 'phone')) {
            Schema::table('follow_ups', function (Blueprint $table) {
                $table->string('phone')->nullable()->after('client_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('follow_ups') && Schema::hasColumn('follow_ups', 'phone')) {
            Schema::table('follow_ups', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }
    }
};

