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
        Schema::table('activity_types', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_types', 'section_title')) {
                $table->string('section_title')->nullable()->after('category');
            }

            if (!Schema::hasColumn('activity_types', 'section_order')) {
                $table->unsignedInteger('section_order')->default(0)->after('section_title');
            }

            if (!Schema::hasColumn('activity_types', 'item_order')) {
                $table->unsignedInteger('item_order')->default(0)->after('section_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_types', function (Blueprint $table) {
            if (Schema::hasColumn('activity_types', 'item_order')) {
                $table->dropColumn('item_order');
            }

            if (Schema::hasColumn('activity_types', 'section_order')) {
                $table->dropColumn('section_order');
            }

            if (Schema::hasColumn('activity_types', 'section_title')) {
                $table->dropColumn('section_title');
            }
        });
    }
};
