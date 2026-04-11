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
            $table->boolean('is_mcq')->default(false)->after('require_user_response');
            $table->text('mcq_question')->nullable()->after('is_mcq');
            $table->json('mcq_options')->nullable()->after('mcq_question');
            $table->integer('mcq_correct_option')->nullable()->after('mcq_options');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_type_daily_logs', function (Blueprint $table) {
            $table->dropColumn(['is_mcq', 'mcq_question', 'mcq_options', 'mcq_correct_option']);
        });
    }
};
