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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'membership_tier')) {
                $table->string('membership_tier')->default('Free')->after('rank');
            }
        });

        Schema::table('activity_types', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_types', 'min_tier')) {
                $table->string('min_tier')->default('Free');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('membership_tier');
        });

        Schema::table('activity_types', function (Blueprint $table) {
            $table->dropColumn('min_tier');
        });
    }
};
