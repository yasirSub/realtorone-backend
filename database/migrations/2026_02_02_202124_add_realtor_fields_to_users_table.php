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
            // Profile fields
            $table->string('mobile')->nullable();
            $table->string('city')->nullable();
            $table->string('brokerage')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->integer('years_experience')->nullable();
            $table->decimal('current_monthly_income', 12, 2)->nullable();
            $table->decimal('target_monthly_income', 12, 2)->nullable();
            $table->boolean('is_profile_complete')->default(false);
            
            // Diagnosis fields
            $table->boolean('has_completed_diagnosis')->default(false);
            $table->string('diagnosis_blocker')->nullable(); // leadGeneration, confidence, closing, discipline
            $table->json('diagnosis_scores')->nullable();
            
            // Performance metrics
            $table->integer('growth_score')->default(0);
            $table->integer('execution_rate')->default(0);
            $table->integer('mindset_index')->default(0);
            $table->string('rank')->nullable();
            
            // Streak tracking
            $table->integer('current_streak')->default(0);
            $table->date('last_activity_date')->nullable();
            
            // Premium
            $table->boolean('is_premium')->default(false);
            $table->timestamp('premium_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mobile',
                'city',
                'brokerage',
                'instagram',
                'linkedin',
                'years_experience',
                'current_monthly_income',
                'target_monthly_income',
                'is_profile_complete',
                'has_completed_diagnosis',
                'diagnosis_blocker',
                'diagnosis_scores',
                'growth_score',
                'execution_rate',
                'mindset_index',
                'rank',
                'current_streak',
                'last_activity_date',
                'is_premium',
                'premium_expires_at',
            ]);
        });
    }
};
