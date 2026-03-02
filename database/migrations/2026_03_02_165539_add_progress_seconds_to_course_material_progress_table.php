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
        Schema::table('course_material_progress', function (Blueprint $table) {
            $table->integer('progress_seconds')->default(0)->after('material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_material_progress', function (Blueprint $table) {
            $table->dropColumn('progress_seconds');
        });
    }
};
