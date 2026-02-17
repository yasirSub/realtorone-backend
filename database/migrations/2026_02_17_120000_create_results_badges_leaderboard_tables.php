<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============== PHASE 2: RESULTS TRACKER ==============
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('type', ['hot_lead', 'deal_closed', 'commission']);
            $table->string('client_name')->nullable();
            $table->string('property_name')->nullable();
            $table->string('source')->nullable(); // bayut, property_finder, instagram, referral, cold_call, walk_in
            $table->decimal('value', 15, 2)->default(0); // commission amount for deals
            $table->string('status')->default('active'); // active, converted, lost
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'type']);
        });

        // ============== PHASE 2: FOLLOW-UP TRACKER ==============
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('result_id')->nullable()->constrained('results')->onDelete('set null');
            $table->string('client_name');
            $table->string('contact_info')->nullable();
            $table->dateTime('due_at');
            $table->dateTime('completed_at')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_overdue')->default(false);
            $table->text('notes')->nullable();
            $table->integer('priority')->default(1); // 1=normal, 2=high, 3=urgent
            $table->timestamps();

            $table->index(['user_id', 'is_completed', 'due_at']);
        });

        // ============== PHASE 2: WEEKLY SCORES ==============
        Schema::create('weekly_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('week_start');
            $table->date('week_end');
            $table->integer('avg_score')->default(0);
            $table->integer('total_activities')->default(0);
            $table->integer('days_active')->default(0); // out of 7
            $table->integer('consistency_percent')->default(0);
            $table->integer('total_leads')->default(0);
            $table->integer('total_deals')->default(0);
            $table->decimal('total_commission', 15, 2)->default(0);
            $table->integer('streak_maintained')->default(0); // 1=yes, 0=no
            $table->timestamps();

            $table->unique(['user_id', 'week_start']);
        });

        // ============== PHASE 4: BADGES ==============
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon')->default('🏆');
            $table->string('color')->default('#FFD700');
            $table->enum('type', ['daily', 'weekly', 'monthly', 'milestone', 'special']);
            $table->json('criteria')->nullable(); // JSON: {metric: "streak", threshold: 7}
            $table->integer('rarity')->default(1); // 1=common, 2=rare, 3=epic, 4=legendary
            $table->timestamps();
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->date('earned_at');
            $table->timestamps();

            $table->unique(['user_id', 'badge_id', 'earned_at']);
        });

        // ============== PHASE 4: LEADERBOARD CACHE ==============
        Schema::create('leaderboard_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category'); // consistency, momentum_climber, deal_maker, revenue, identity_discipline
            $table->string('period'); // daily, weekly, monthly
            $table->date('period_date');
            $table->integer('score')->default(0);
            $table->integer('rank')->default(0);
            $table->json('metadata')->nullable(); // extra context
            $table->timestamps();

            $table->index(['category', 'period', 'period_date', 'rank']);
            $table->unique(['user_id', 'category', 'period', 'period_date']);
        });

        // Add longest_streak to users if not exists
        if (!Schema::hasColumn('users', 'longest_streak')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('longest_streak')->default(0)->after('current_streak');
            });
        }

        // Add total_commission to users for quick access
        if (!Schema::hasColumn('users', 'total_commission')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('total_commission', 15, 2)->default(0)->after('longest_streak');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'total_commission')) {
                $table->dropColumn('total_commission');
            }
        });
        
        Schema::dropIfExists('leaderboard_cache');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('weekly_scores');
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('results');
    }
};
