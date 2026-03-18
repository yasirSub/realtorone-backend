<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_types', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_types', 'script_idea')) {
                $table->text('script_idea')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activity_types', function (Blueprint $table) {
            if (Schema::hasColumn('activity_types', 'script_idea')) {
                $table->dropColumn('script_idea');
            }
        });
    }
};
